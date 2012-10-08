#!/usr/bin/perl
# Copyright (c) 2002-2007 Sampo Kellomaki (sampo@iki.fi). All Rights Reserved.
# This is free software. You may distribute under GPL. NO WARRANTY.
# $Id: xsd2sg.pl,v 1.26 2007-02-20 21:39:35 sampo Exp $
# 9.6.2005,   Sampo Kellomaki <sampo@symlabs.com>
# 15.7.2005,  drastically improved the support for -s flag --Sampo
# 16.8.2005,  debugged for MM7 schema parsing --Sampo
# 25.11.2005, added union() support --Sampo
# 3.2.2006,   added support for xs:redefine and mixed(1) --Sampo
# 12.5.2006,  added verbatim() file inclusion and inline xsd() features --Sampo
# 27.5.2006,  took a stab at adding some generation support --Sampo
# 29.5.2006,  complete redesign from basis of simple recursive descent parser --Sampo
# 6.8.2006,   continued adding code generation --Sampo
# 8.8.2006,   new initialization scheme for namespaces and tokens --Sampo
# 26.8.2006,  code generation debugging; fixes to simple elems --Sampo
# 5.9.2006,   several fixes to allow Liberty ID-FF 1.2 schemata to pass --Sampo
# 14.9.2006,  tweaks for ID-WSF 2.0 schemata to pass --Sampo
# 23.9.2006,  WO encoder support, improve namespaces, exc-c14n --Sampo
# 15.10.2006, refactor sources to be per namespace --Sampo
#
# XML schema to Schema Grammar converter (and beautifier)

$usage = <<USAGE;
Usage: ./xsd2sg.pl [OPTIONS] <schama.xsd >schame.sg
Usage: ./xsd2sg.pl [OPTIONS] -s <schama.sg >schame.xsd
Usage: ./xsd2sg.pl [OPTIONS] -gen FF -p PP -r N1:E1 -r N2:E1 -S schama1.sg schema2.sg >/dev/null
   -s          Convert schema grammar (sg) to xsd (default
               is the other way around)
   -S          Multigrammar coversion (for generation)
   -noverbatim Disable verbatim XSD includes
   -gen FF     Generate datatypes, encoders, and decoders. Files are named with prefix FF
   -p PP       Specify prefix used, in addition to ns, for datatypes of automatic code generation.
   -ext ns     Consider namespace prefix ns to be externally satisfied dependency
   -r node     Generate code starting from specified root node.
   -z zx       Define zx prefix.
   -d          Increase debugging level.
   -h          Show this help
   -H          Show summary of schema grammar format
USAGE
    ;

$format = <<FORMAT;

Schema grammar introduces following notions

  ee          Bareword signifies an XML element
  @aa         At (@) prefix signifies an XML attribute
  %tt         Percent (%) prefix signifies a complexType
  &gg         Ampersand (&) prefix a signifies group
  &@ag        Ampersand and at (&@) prefix signifies attributeGroup
  xx -> %tt   Arrow (->) signifies reference to type that defines element or attribute
  xx: ... ;   Colon (:) means that the definition of type follows immediately
  ee          An element or attribute by itself means exactly one occurance is expected
  ee?         Question mark (?) means the element or attribute is optional
  ee*         Asterisk (*) means the element may appear from zero to infinite
              number of times (same as * in regular expressions)
  ee+         Plus (+) means the element must appear at least once, but
              may appear an infinite number of times (same as + in regular expressions)
  ee{x,y}     The element must appear between x and y times (same as in regex)
  ee | ee     The pipey symbol (|) means elements are mutually exclusive choices.
  ee ee       Concatenation of elements or attributes means sequence
  base( t )   Introduce Extension base type (derive a type)
  redef( .. ) Redefine a type (using <xs:redefine> construct)
  mixed(1)    Mark a complex type as having mixed content type, i.e. strings and elements alternate
  enum( ... ) Introduce enumeration of xs:strings
  ns( ... )   Introduce namespace (usually of any or @any)
  union( .. ) Union of types
  all         ??
  any         xs:any, the XML arbitrary element extension mechanism
  @any        xs:anyAttribute, the XML arbitrary attribute extension mechanism
  verbatim(f) Include a file verbatim to xsd output.
  xsd(x)      Insert the x in verbatim to the xsd output. 

For example:

target(demo, urn:demo.com:demo:0.1)
import(foo,  urn:demo.com:foo:0.2, foo.xsd)
verbatim(copyright-annotation.xsf)

ResourceID -> %disco:ResourceIDType                    // element definition by reference to type
&ResourceIDGroup:  ResourceID | EncryptedResourceID    // group with choice (one of)
ChangeFormat:  enum( ChangedElements, CurrentElements )

@changeFormat: enum( ChangedElements CurrentElements All )   // xs:string base is implied

ItemData -> %ItemDataType
%ItemDataType:
  any*                    // 0-n any elements
  @id -> %xs:ID
  @itemIDRef -> %IDReferenceType
  @notSorted: enum( Now Never )
  @changeFormat
  ;
Query -> %QueryType
%QueryType:
  &ResourceIDGroup
  Subscription?
  QueryItem*: base(ResultQueryType)
    @count -> xs:nonNegativeInteger
    @offset -> xs:nonNegativeInteger default(0)
    @setID -> %IDType
    @setReq: enum( Static DeleteSet )
  Extension*
  @id -> xs:ID
  @any namespace( "##other")
  ;
FORMAT
    ;

$copyright_holder = 'Sampo Kellomaki (sampo@iki.fi)';
#$copyright_holder = 'Symlabs (symlabs@symlabs.com)';
$copyright_msg = <<COPYRIGHT;
/* Code generation design Copyright (c) 2006 $copyright_holder,
 * All Rights Reserved. NO WARRANTY. See file COPYING for terms and conditions
 * of use. Some aspects of code generation were driven by schema
 * descriptions that were used as input and may be subject to their own copyright.
 * Code generation uses a template, whose copyright statement follows. */
COPYRIGHT
;

use Data::Dumper;

sub inc_out {    my ($x) = @_;    $inc .= $x;    warn "inc_out: $x" if $trace>2; }
sub sg_out  {    my ($x) = @_;    $sg  .= $x;    warn "sg_out: $x"  if $trace>2; }
sub xs_out  {    my ($x) = @_;    $xsd .= $x;    warn "xs_out: $x"  if $trace>2; }
sub hdr_out {    my ($x) = @_;    $hdr .= $x;    warn "hdr_out: $x" if $trace>2; }
sub enc_out {    my ($x) = @_;    $enc .= $x;    warn "enc_out: $x" if $trace>2; }
sub dec_out {    my ($x) = @_;    $dec .= $x;    warn "dec_out: $x" if $trace>2; }
sub aux_out {    my ($x) = @_;    $aux .= $x;    warn "aux_out: $x" if $trace>2; }
sub getput_out { my ($x) = @_;    $getput .= $x; warn "getput_out: $x" if $trace>2; }
sub ns_out  {    my ($x) = @_;    $nsout .= $x;  warn "ns_out: $x"  if $trace>2; }
sub nsh_out {    my ($x) = @_;    $nshout .= $x; warn "nsh_out: $x" if $trace>2; }
sub elems_gperf_out { my ($x) = @_; $elems_gperf .= $x; warn "elems_gperf_out: $x" if $trace>2; }
sub attrs_gperf_out { my ($x) = @_; $attrs_gperf .= $x; warn "attrs_gperf_out: $x" if $trace>2; }

sub reset_accumulators { $hdr = $enc = $dec = $aux = $getput = ''; }

%rename = ( case => 'case_is_c_keyword', signed => 'signed_is_c_keyword' );

die $usage  if $ARGV[0] eq '-h';
die $format if $ARGV[0] eq '-H';

$nsix = 0;
undef $/;

while ($ARGV[0] eq '-d') {
    ++$trace;
    shift;
}

$zx = 'zx';

if ($ARGV[0] eq '-z') {      shift;  $zx = shift; }
if ($ARGV[0] eq '-noverbatim') {    ++$noverbatim;    shift; }
if ($ARGV[0] eq '-gen')  {   shift;  $gen_prefix = shift; }
if ($ARGV[0] eq '-p')    {   shift;  $tx = shift; }
while ($ARGV[0] eq '-ext') { shift; $ext_ns = shift; $ignore_ext{$ext_ns} = 1; }
while ($ARGV[0] eq '-r')   { shift; push @roots, shift; }

if ($ARGV[0] eq '-s') {   # simple grammar to schema mode
    $x = <STDIN>;
    sg_to_xsd();
    exit;
}

$ZX = uc $zx;

if ($ARGV[0] eq '-S') {   # Multifile grammar to schema mode (useful for generation)
    shift;
    for  $file (@ARGV) {
	open F, $file or die "Cant read file($file): $!";
	$x = <F>;
	close F;
	warn "Processing($file)" if $trace;
	sg_to_xsd();
    }
    warn "Generating" if $trace;
    generate() if $gen_prefix;
    exit;
}

sub sg_to_xsd {
    $ns = '';
    #                     1   1      2 URL   2
    if ($x =~ /target\((?:(\w+),\s*)?([^\)]+?)\)/) {
	$ns .= qq(    targetNamespace="$2"\n);
	$ns .= qq(    xmlns:$1="$2"\n) if length($1);
	++$ns_siz;
	ns_out qq({ 0, 0, 0, 0, 0, 0, sizeof("$1")-1, "$1", sizeof("$2")-1, "$2"  },\n);
	nsh_out <<NSH;
#define ${tx}xmlns_ix_$1 $nsix
#define ${tx}xmlns_$1    "$2"
NSH
    ;
	++$nsix;
	die "Inconsistent namespace URIs for prefix($1) old($ns_tab{$1}) new($2)"
	    if defined($ns_tab{$1}) && $ns_tab{$1} ne $2;
	$ns_tab{$1} = $2;
	$cur_ns = "$1:";
	$x =~ s/target\([^\)]+\)//;
    }

    $x =~ s/xsd\(([^\),]+)\)/sg_fold($1, 'xsd')/gsex;
    $x =~ s/\#((end)?sec\(\w+\))/$1/g;
    $x =~ s/^\#\#(.+)$/sg_fold($1, 'comment')/gmex;
    $x =~ s/\n\s*\#[^\n]*//g;   # Eat away all comments lines
    $x =~ s/^\s*\#[^\n]*//gs;   # Eat away all comments lines
    #$x =~ s/\n\s*\#[^\n]*\n/\n/g;   # Eat away all comments lines (perl bug?)
    $x =~ s/\s+\#[^\n]*\n/\n/g;     # Zap end of line comments

    #           1 prefx 1    2 URL   2
    $x =~ s/ns\(([^\),]+),\s*([^\),]+)\)/sg_ns($1, $2)/gex;
    #                  1   1      2 URL   2    3 file  3
    $x =~ s/import\((?:(\w+),\s*)?([^\),]+),\s*([^\),]+)\)/sg_import($1, $2, $3)/gex;
    $x =~ s/include\(([^\),]+)\)/sg_include($1)/gex;

    $x =~ s/(\w)-(\w)/$1_$2/g;
    $x =~ s/redef\(([^\),]+)\)/sg_fold($1, 'redef')/gex;
    $x =~ s/verbatim\(([^\),]+)\)/sg_fold($1, 'verbatim')/gex;

    #warn "--[$x]--";
    $x =~ s/:([^a-z0-9\/])/.$1/gsi; # Type definition colon
    $x =~ s/(\w)\s+(\w)/$1!$2/gs;   # Trivial dividers
    $x =~ s/\s+//gs;
    #warn "X($x)";
    
    $x =~ s/enum\s*\((.*?)\)/'enum(' . fold_enum_value($1) . ')'/sge;

    warn "Processed and folded sg($x)" if $trace;

    @tok = $x =~ /((?:\w+\()|(?:\#\#\w+)|(?:[\w:]+)|(?:->%)|(?:&\@)|[.;,{}|\)\%&\@*+?!])/g;
    if ($trace) { for ($i = 0; $i <= $#tok; ++$i) { warn "sg $i: ($tok[$i])\n"; } }
    $i = 0;
    ++$i if $tok[$i] =~ /^\s*$/;

    sg_top('  ');
    
    $xsd =~ s%\n\s*<xs:sequence>\n\s*</xs:sequence>%%gs;  # peep hole silly
    $xsd =~ s%<xs:extension([^>]*?)>\s*</xs:extension>%<xs:extension$1/>%gs;  # peep hole
    
    print <<XSD;
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema
$ns    xmlns:xs="http://www.w3.org/2001/XMLSchema"
    elementFormDefault="qualified"
    attributeFormDefault="unqualified">
$xsd</xs:schema>
XSD
    ;
}

sub fold_enum_value {
    my ($x) = @_;
    $x =~ s/([^A-Za-z0-9! ])/sprintf "0x%02x",ord($1)/gse;
    return $x;
}

sub sg_fold {
    my ($x,$tag) = @_;
    $x =~ s/(.)/sprintf "%02x",ord($1)/gsex;
    return qq{$tag($x)};
}

sub sg_import {
    my ($prefix, $url, $file) = @_;
    sg_ns($prefix, $url) if length $prefix;
    xs_out qq(  <xs:import namespace="$url"\n      schemaLocation="$file"/>\n);
    return '';
}

sub sg_include {
    my ($url) = @_;
    xs_out qq(  <xs:include schemaLocation="$url"/>\n);
    return '';
}

sub sg_ns {
    my ($prefix, $url) = @_;
    $ns .= qq(    xmlns:$prefix="$url"\n);
    die "Inconsistent namespace URIs for prefix($prefix) old($ns_tab{$prefix}) new($url)"
	if defined($ns_tab{$prefix}) && $ns_tab{$prefix} ne $url;
    #warn "prefix($prefix) url($url)";
    $ns_tab{$prefix} = $url;
    return '';
}

####################################################
### Schema Grammar to XML schema

sub sg_top {
    my ($indent) = @_;
    while ($i <= $#tok) {
	warn $indent."sg_top $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
	if ($tok[$i] eq '%')          {  sg_complexType($indent);
	} elsif ($tok[$i] eq '&')     {  sg_group($indent);
	} elsif ($tok[$i] eq '&@')    {  sg_attr_group($indent);
	} elsif ($tok[$i] eq '@')     {  sg_attr($indent);
	} elsif ($tok[$i] =~ /^[\w:]+$/) {  sg_elem($indent);
        } elsif ($tok[$i] eq '!')     {  ++$i;  # Divider between decls
	} elsif ($tok[$i] eq 'comment(')  {
	    ++$i;
	    ($x = $tok[$i]) =~ s/(..)/chr(hex($1))/gex;
	    xs_out qq{<!-- $x -->\n};
	    ++$i;
	    die "Expected ) after comment $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
		unless $tok[$i] eq ')';
	    ++$i;
	} elsif ($tok[$i] eq 'sec(')  {
	    ++$i;
	    xs_out qq{<!--sec($tok[$i])-->\n};
	    ++$i;
	    die "Expected ) after sec $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
		unless $tok[$i] eq ')';
	    ++$i;
	} elsif ($tok[$i] eq 'endsec(')  {
	    ++$i;
	    xs_out qq{<!--endsec($tok[$i])-->\n};
	    ++$i;
	    die "Expected ) after endsec $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
		unless $tok[$i] eq ')';
	    ++$i;
	} elsif ($tok[$i] eq 'verbatim(')  {
	    ++$i;
	    unless ($noverbatim) {
		($x = $tok[$i]) =~ s/(..)/chr(hex($1))/gex;
		open F, "<$x" or die "verbatim($x) not found: $!";
		$x = <F>;
		close F;
		xs_out $x;
	    }
	    ++$i;
	    die "Expected ) after verbatim $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
		unless $tok[$i] eq ')';
	    ++$i;
	} elsif ($tok[$i] eq 'xsd(')  {
	    ++$i;
	    ($x = $tok[$i]) =~ s/(..)/chr(hex($1))/gex;
	    xs_out $x;
	    ++$i;
	    die "Expected ) after xsd $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
		unless $tok[$i] eq ')';
	    ++$i;
        } else  { die "Bad token $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";  }
    }
}

sub sg_elem {
    my ($indent) = @_;
    my $ename = $tok[$i];
    xs_out qq($indent<xs:element name="$ename");
    ++$i;
    warn $indent."sg_elem $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    $dt{'element'}{"$cur_ns$ename"} = sg_type_ref_or_def($indent, 'element');
}

sub sg_attr {
    my ($indent) = @_;
    ++$i;
    warn $indent."sg_attr $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    my $aname = sg_get_word();
    xs_out qq($indent<xs:attribute name="$aname");
    $dt{'attribute'}{"$cur_ns$aname"} = sg_type_ref_or_def($indent, 'attribute');
}

sub sg_group {
    my ($indent) = @_;
    ++$i;
    warn $indent."sg_group $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    my $gname = sg_get_word();
    xs_out qq($indent<xs:group name="$gname");
    die "Expected . (:) after group name $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" unless $tok[$i] eq '.';
    $dt{'group'}{"$cur_ns$gname"} = sg_type_def($indent, 'group');
}

sub sg_attr_group {
    my ($indent) = @_;
    ++$i;
    warn $indent."sg_attr_group $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    my $agname = sg_get_word();
    xs_out qq($indent<xs:attributeGroup name="$agname");
    die "Expected . (:) after attr group name $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" unless $tok[$i] eq '.';
    $dt{'attributeGroup'}{"$cur_ns$agname"} = sg_type_def($indent, 'attributeGroup');
}

sub sg_complexType {
    my ($indent) = @_;
    ++$i;
    my $mixed = '';
    my $redef = 0;
    my $name = sg_get_word();
    die "Expected . (:) after complexType name($name) $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" unless $tok[$i] eq '.';
    warn $indent."sg_complexType $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    if ($tok[$i + 1] eq 'redef(' && $tok[$i + 3] eq ')') {
	($x = $tok[$i+2]) =~ s/(..)/chr(hex($1))/gex;
	xs_out qq($indent<xs:redefine schemaLocation="$x">\n);
	$indent .= '  ';
	$redef = 1;
	$i += 3;
	die "Expected ) after redef name($name) $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" unless $tok[$i] eq ')';
	$tok[$i] = '.';
	warn $indent."sg_complexType after redef $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    }
    if ($tok[$i + 1] eq 'mixed(' && $tok[$i + 3] eq ')') {
	$mixed = qq( mixed="$tok[$i+2]");
	$i += 3;
	die "Expected ) after mixed $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" unless $tok[$i] eq ')';
	$tok[$i] = '.';
	warn $indent."sg_complexType after mixed $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    }
    warn $indent."sg_complexType BEFORE BASE DETECT $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    if (0 && $tok[$i + 1] eq 'base(' && $tok[$i + 3] eq ')'
	&& $tok[$i+2] =~ /^xs:/) {  # && $tok[$i + 4] eq ';'   All derivations of simple base are still simple
	warn "simpleType in disguise($tok[$i+2])";
	xs_out qq($indent<xs:simpleType name="$name");
	$dt{'complexType'}{"$cur_ns$name"} = sg_type_def($indent, 'simpleType');
    } else {
	xs_out qq($indent<xs:complexType$mixed name="$name");
	$dt{'complexType'}{"$cur_ns$name"} = sg_type_def($indent, 'complexType');
    }
    if ($redef) {
	substr($indent, -2) = '';
	xs_out qq($indent</xs:redefine>\n);
    }
}

# Type specific handlers

sub sg_any {
    my ($indent) = @_;
    ++$i;
    my ($occurs, $n_min, $n_max) = sg_occurs();
    my ($ns, $pc);
    warn $indent."sg_any $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    if ($tok[$i] eq '!') {
	++$i;
	die "What is this $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
    }
    if ($tok[$i] eq 'ns(' || $tok[$i] eq 'processContents(') {
	while ($i <= $#tok) {
	    if ($tok[$i] eq 'processContents(') {
		++$i;
		my $w = sg_get_word();
		$pc .= qq( processContents="$w");
		die "Expected ) after ns decl $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
		    unless $tok[$i] eq ')';
		++$i;
		next;
	    } elsif ($tok[$i] eq 'ns(') {
		++$i;
		$ns .= qq( namespace="$tok[$i]");
		++$i;
		die "Expected ) after ns decl $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
		    unless $tok[$i] eq ')';
		++$i;
		next;
	    }
	    last;
	}
    }
    $ns = qq( namespace="##any") if !$ns;
    xs_out qq($indent<xs:any$occurs$pc$ns/>\n);    
    return "any\$$n_min\$$n_max\$$pc\$$ns";
}

sub sg_any_attr {
    my ($indent) = @_;
    my ($occurs, $n_min);
    ++$i;
    $indent.warn "sg_any_attr $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    if ($tok[$i] eq '?') {
	$n_min = 0;
	$occurs = qq( use="optional");
	++$i;
    } else {
	$n_min = 1;
	$occurs = qq( use="required");
    }
    # *** should process ns() if present
    # N.B. anyAttribute can not take occurs spec
    xs_out qq($indent<xs:anyAttribute namespace="##other" processContents="lax"/>\n);
    return "anyAttribute\$$n_min\$1";
}

sub sg_enum {
    my ($indent, $close) = @_;
    ++$i;
    my $datarep = 'enum';
    my $val = $tok[$i];
    $val =~ s/0x([0-9a-f][0-9a-f])/chr(hex($1))/gse;  # unfold
    
    xs_out qq($indent  <xs:simpleType>\n);
    xs_out qq($indent    <xs:restriction base="xs:string">\n);
    xs_out qq($indent      <xs:enumeration value="$val"/>\n);
    ++$i;
    $datarep .= '$'. $val;

    warn $indent."sg_enum $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    while ($i <= $#tok && $tok[$i] ne ')') {
	die "Expected ! in enum $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" unless $tok[$i] eq '!';
	++$i;
	$val = $tok[$i];
	$val =~ s/0x([0-9a-f][0-9a-f])/chr(hex($1))/gse;  # unfold
	xs_out qq($indent      <xs:enumeration value="$val"/>\n);
	++$i;
	$datarep .= '$' . $val;
    }

    xs_out qq($indent    </xs:restriction>\n);
    xs_out qq($indent  </xs:simpleType>\n);
    xs_out qq($indent</xs:$close>\n);

    die "Expected ) after enum type $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
	unless $tok[$i] eq ')';
    ++$i;
    die "Expected ; after enum type $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
	unless $tok[$i] eq ';';
    ++$i;
    return $datarep;
}

sub sg_union {
    my ($indent, $close) = @_;
    ++$i;
    my $datarep = 'union';
    
    xs_out qq($indent  <xs:simpleType>\n);

    warn $indent."sg_union $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    my $val = '';
    for (; $i <= $#tok && $tok[$i] ne ')'; ++$i) {
	$val .= $tok[$i] . ' ';
	$datarep .= '$' . $tok[$i];
    }
    chop $val;
    xs_out qq($indent    <xs:union memberTypes="$val"/>\n);

    xs_out qq($indent  </xs:simpleType>\n);
    xs_out qq($indent</xs:$close>\n);

    die "Expected ) after union type $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
	unless $tok[$i] eq ')';
    ++$i;
    die "Expected ; after union type $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
	unless $tok[$i] eq ';';
    ++$i;
    return $datarep;
}

sub sg_choice {
    my ($indent, $close) = @_;
    my $datarep = 'choice';
    xs_out qq($indent  <xs:choice>\n);

    my $alt = sg_get_word();  # *** groups can be members of choice as well?
    xs_out qq($indent    <xs:element ref="$alt"/>\n);
    $datarep .= '$' . $alt;
    
    warn $indent."sg_choice $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    while ($i <= $#tok && $tok[$i] ne ';') {
	die "Expected | in alternate $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" if $tok[$i] ne '|';
	++$i;
	my $alt = sg_get_word();  # *** groups can be members of choice as well?
	xs_out qq($indent    <xs:element ref="$alt"/>\n);
	$datarep .= '$' . $alt;
    }

    xs_out qq($indent  </xs:choice>\n);
    xs_out qq($indent</xs:$close>\n);
    die "Expected ; after choice $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
	unless $tok[$i] eq ';';
    ++$i;
    return $datarep;
}

sub sg_type_def {
    my ($indent, $close) = @_;
    my ($base_type, $closed, $basecontent);
    my @data_ary;
    die "Expected . (:) before type def ($close) $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
	unless $tok[$i] eq '.';
    ++$i;
    xs_out ">\n";

    warn $indent."sg_type_def($close) $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    return sg_choice($indent, $close) if $tok[$i + 1] eq '|';
    return sg_enum($indent, $close) if $tok[$i] eq 'enum(';
    return sg_union($indent, $close) if $tok[$i] eq 'union(';

    if ($close ne 'complexType' && $close ne 'attributeGroup' && $close ne 'simpleType') {
	xs_out qq($indent  <xs:complexType>\n);
	$indent .= '    ';
    } else {
	$indent .= '  ';
    }
    if ($tok[$i] eq 'base(') {
	++$i;
	$base_type = $tok[$i];
	++$i;
	die "Expected ) after base type ($close) $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
	    unless $tok[$i] eq ')';
	if ($base_type =~ /string$/) {	       $basecontent = 'simpleContent';
	} elsif ($base_type =~ /base64Binary$/) {   $basecontent = 'simpleContent';
	} elsif ($base_type =~ /[Ss]hort$/) {  $basecontent = 'simpleContent';
	} elsif ($base_type =~ /nteger$/) {    $basecontent = 'simpleContent';
	} elsif ($base_type =~ /boolean$/) {   $basecontent = 'simpleContent';
	} elsif ($base_type =~ /anyURI$/) {    $basecontent = 'simpleContent';
	} elsif ($base_type =~ /date$/) {      $basecontent = 'simpleContent';
	} elsif ($base_type =~ /gMonthDay$/) { $basecontent = 'simpleContent';
	} else {                               $basecontent = 'complexContent';
	}
	if ($basecontent eq 'simpleContent') {
	    push @data_ary, "_d\$\$0\$1\$$base_type";  # pseudo child element to represent content
	} else {
	    push @data_ary, "base\$\$0\$1\$$base_type";  # generate reference to base
	    ++$needed_complexType{$base_type};
	}
	xs_out qq($indent<xs:$basecontent>\n);
	xs_out qq($indent  <xs:extension base="$base_type">\n);
	$indent .= '    ';
	++$i;
    }
    
    xs_out "$indent<xs:sequence>\n";
    warn $indent."sg_type_def($close) sequence $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    while ($i <= $#tok && $tok[$i] ne ';') {
	if      ($tok[$i] eq 'any') {
	    push @data_ary, sg_any("  $indent");
	} elsif ($tok[$i] =~ /^[\w:]+$/)  {
	    push @data_ary, sg_ref_def_or_nada("  $indent", 'element');
	} elsif ($tok[$i] eq '&')   {
	    ++$i;
	    push @data_ary, sg_ref_def_or_nada("  $indent", 'group');
	} elsif ($tok[$i] eq '&@')  {
	    xs_out "$indent</xs:sequence>\n" if !$closed;
	    $closed = 1;
	    ++$i;
	    push @data_ary, sg_ref_def_or_nada($indent, 'attributeGroup');
	} elsif ($tok[$i] eq '@')   {
	    xs_out "$indent</xs:sequence>\n" if !$closed;
	    $closed = 1;
	    ++$i;
	    if ($tok[$i] eq 'any')  { push @data_ary, sg_any_attr($indent);
	    } else { push @data_ary, sg_ref_def_or_nada($indent, 'attribute');  }
        } elsif ($tok[$i] eq '!')   { ++$i; # Divider between sequence elems
        } else { die "Bad token in type def ($close) $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"; }
    }
    xs_out "$indent</xs:sequence>\n" if !$closed;

    if ($base_type) {
	substr($indent, -4) = '';
	xs_out qq($indent  </xs:extension>\n);
	xs_out qq($indent</xs:$basecontent>\n);
    }
    if ($close ne 'complexType' && $close ne 'attributeGroup' && $close ne 'simpleType') {
	substr($indent, -4) = '';
	xs_out qq($indent  </xs:complexType>\n);
    } else {
	substr($indent, -2) = '';
    }
    xs_out "$indent</xs:$close>\n";
    ++$i;
    return \@data_ary;
}

sub sg_type_ref_or_def {
    my ($indent, $close) = @_;
    warn $indent."sg_type_ref_or_def $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    if      ($tok[$i] eq '->%') {  return sg_type_ref();
    } elsif ($tok[$i] eq '.')   {  return sg_type_def($indent, $close);
    } else { die "Bad token in $close $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"; }
}

sub sg_type_ref {
    die "Expected ->% before complexType $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
	unless $tok[$i] eq '->%';
    ++$i;
    warn "sg_type_ref $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    my $default;
    my $r = sg_get_word();
    if ($tok[$i] eq '!' && $tok[$i+1] eq 'default(') {
	++$i;
	++$i;
	$default = qq( default="$tok[$i]");
	++$i;
	die "Expected ) after default $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"
	    unless $tok[$i] eq ')';
	++$i;
    }
    xs_out qq( type="$r"$default/>\n);
    return "ref\$$r\$$default";
}

sub sg_ref_def_or_nada {
    my ($indent, $tag) = @_;
    my ($occurs, $n_min, $n_max);
    warn $indent."sg_ref_def_or_nada($tag) $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])\n" if $trace>1;
    my $name = sg_get_word();
    if ($tag eq 'attribute') {
	$n_max = 1;
	if ($tok[$i] eq '?') {
	    $n_min = 0;
	    $occurs = qq( use="optional");
	    ++$i;
	} else {
	    $n_min = 1;
	    $occurs = qq( use="required");
	}
    } elsif ($tag eq 'attributeGroup') {
	$occurs = '';
    } else {
	($occurs, $n_min, $n_max) = sg_occurs();
    }
    if      ($tok[$i] eq '.')   {  # inline type definition
	xs_out qq($indent<xs:$tag name="$name"$occurs);
	$dt{$tag}{$name} = sg_type_def($indent, $tag);
	return "$tag\$$name\$$n_min\$$n_max";
    } elsif ($tok[$i] eq '->%') {  # reference to a type
	xs_out qq($indent<xs:$tag name="$name"$occurs);
	$dt{$tag}{$name} = sg_type_ref();
	return "$tag\$$name\$$n_min\$$n_max";
    } else {                       # reference to ???
	xs_out qq($indent<xs:$tag ref="$name"$occurs/>\n);
	return "$tag\$$name\$$n_min\$$n_max\$nada";
    }
}

sub sg_occurs {
    if    ($tok[$i] eq '?') { ++$i;  return (qq(\tminOccurs="0" maxOccurs="1"), 0, 1); }
    elsif ($tok[$i] eq '*') { ++$i;  return (qq(\tminOccurs="0" maxOccurs="unbounded"), 0, -1); }
    elsif ($tok[$i] eq '+') { ++$i;  return (qq(\tminOccurs="1" maxOccurs="unbounded"), 1, -1); }
    elsif ($tok[$i] eq '{') {
	++$i;
	my $b = sg_get_word();
	die "Expected , in occurs $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" if $tok[$i] ne ',';
	++$i;
	if ($tok[$i] eq '}') {
	    ++$i;
	    return qq(\tminOccurs="$b" maxOccurs="unbounded");
	}
	my $e = sg_get_word();
	die "Expected } in occurs $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" if $tok[$i] ne '}';
	++$i;
	return (qq(\tminOccurs="$b" maxOccurs="$e"), $b, $e);
    }
    return (qq(\tminOccurs="1" maxOccurs="1"), 1, 1); # Exactly once if no quantifier was specified
}

sub sg_get_word {
    die "Expected a word token $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" if $tok[$i] !~ /^[\w:]+$/;
    return $tok[$i++];
}

####################################################
### XML schema to SG
### Lexical analysis: split the blob to tokens

sub fold_attr_value {
    my ($x) = @_;
    $x =~ s/ /\\x20/gs;
    return $x;
}

$x = <STDIN>;
$x =~ s%<\?xml.*?>%%gs;
$x =~ s%<!DOCTYPE.*?>%%gs;
$x =~ s%<((\w+:)?documentation)>(.*?)</\1>%%gs;  # Zap pesky documentation
$x =~ s%<!--(.*?)-->%%gs;
$x =~ s%([/?]?>)% $1 %gs;  # We need to see close tag angle bracket as a separate token
$x =~ s%=(['"])([^"'].*?)\1%"=$1" . fold_attr_value($2) . $1 %gse;  #';  #";

#warn $x;

@tok = split /\s+/s, $x;
if ($trace) { for ($i = 0; $i <= $#tok; ++$i) { warn "$i: ($tok[$i])\n"; } }
$i = 0;
++$i if $tok[$i] =~ /^\s*$/;

###
### Top down recursive descent parser for XML schemas
###

top_decls('');   # start the parser ball rolling

sub top_decls {
    my ($indent) = @_;
    scan_pi($indent) if $tok[$i] =~ /^<\?xml$/;
    schema($indent, $1) if $tok[$i] =~ /^<(\w+:)?schema$/;
}

sub scan_pi {
    my ($indent) = @_;
    attrs();
    ++$i until $tok[$i] eq '?>';
    ++$i;
}

sub schema {
    my ($indent, $tagns) = @_;
    die "schema element expected $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" if $tok[$i] !~ /^<(\w+:)?schema$/;

    my $at = attrs($indent.'    ');
    ++$i;
    sg_out("target($$at{'targetNamespace'})\n") if $$at{'targetNamespace'};

    while ($i <= $#tok && $tok[$i] !~ m%</(\w+:)?schema%) {
	#warn "TOP $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
	if ($tok[$i] =~ /^<(\w+:)?import$/) {              import_decl($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?include$/) {        include($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?annotation$/) {     annotation($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?element$/) {        element($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?group$/) {          group($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?attributeGroup$/) { attributeGroup($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?attribute$/) {      attribute($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?complexType$/) {    complexType($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?simpleType$/) {     simpleType($indent);
	} else {
	    die "Unexpected child element in schema $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
	}
    }
}

sub attrs {
    my ($indent) = @_;
    my %at;
    for (++$i; $i <= $#tok; ++$i) {
	my ($ns, $attr, $quote, $val) = $tok[$i] =~ /^(?:([\w-]+):)?([\w-]+)\s*=\s*([\'\"])(.*?)\3$/
	    or last;
	$val =~ s/\\x([0-9a-f][0-9a-f])/chr(hex($1))/gse;  # unfold
	namespace($attr, $val) if $ns eq 'xmlns';
	namespace('', $val) if $ns eq '' && $attr eq 'xmlns';  # Default namespace
	if ($ns) {
	    $at{"$ns:$attr"} = $val;
	} else {
	    $at{$attr} = $val;
	}
    }
    die "junk after attrs $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" if $tok[$i] !~ />$/;

    # Compute occurs as regexp quantifier character
    my $min = $at{'minOccurs'};
    $min = 1 if !defined $min;
    my $max = $at{'maxOccurs'};
    $max = 1 if !defined $max;

    if ($min == 0) {
	if ($max eq 'unbounded') {
	    $at{'occurs'} = '*';
	} elsif ($max == 1) {
	    $at{'occurs'} = '?';
	} else {
	    $at{'occurs'} = "{$min,$max}";
	}
    } elsif ($min == 1) {
	if ($max eq 'unbounded') {
	    $at{'occurs'} = '+';
	} elsif ($max == 1) {
	    $at{'occurs'} = '';  # exactly once
	} else {
	    $at{'occurs'} = "{$min,$max}";
	}
    } else {
	    $at{'occurs'} = "{$min,$max}";
    }
    return \%at;
}

sub namespace {
    my ($ns, $uri) = @_;
    $ns{$ns} = $uri;
    sg_out("ns($ns,$uri)\n") unless $ns eq 'xs';
}

sub close_tag {
    my ($indent,$tag) = @_;
    die "missing close $tag $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" if $tok[$i] !~ m%^</(\w+:)?(\w+)$%;
    die "mismatching close $tag $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" if $2 ne $tag;
    ++$i;
    die "junk at close $tag $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" if $tok[$i] ne '>';
    ++$i;
}

sub process_empty_element {
    my ($indent, $tag) = @_;
    my $at = attrs($indent.'    ');
    if ($tok[$i] eq '>') {
	++$i;
	if ($tok[$i] =~ /^<(\w+:)?annotation$/) {     annotation($indent); }
	close_tag($indent, $tag);
    } else {
	die "junk at end of $tag $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])" if $tok[$i] ne '/>';
	++$i;
    }
    return $at;
}

sub import_decl {
    my ($indent) = @_;
    my $at = process_empty_element($indent, 'import');
    sg_out($indent."import($$at{'namespace'},$$at{'schemaLocation'})\n");
}

sub include {
    my ($indent) = @_;
    my $at = process_empty_element($indent, 'include');
    sg_out($indent."include($$at{'schemaLocation'})\n");
}

sub enumeration {
    my ($indent) = @_;
    my $at = process_empty_element($indent, 'enumeration');
    return $$at{'value'};
}

sub any {
    my ($indent) = @_;
    my $at = process_empty_element($indent, 'any');
    my $ns = defined($$at{'namespace'}) && $$at{'namespace'} ne '##any'
	? '  ns(' . $$at{'namespace'} . ')' : '';
    $ns .= '  processContents(' . $$at{'processContents'} . ')'
	if defined $$at{'processContents'};
    sg_out($indent.'any' . $$at{'occurs'} . $ns . "\n");
}

sub anyAttribute {
    my ($indent) = @_;
    my $at = process_empty_element($indent, 'any');
    my $ns = defined($$at{'namespace'}) && $$at{'namespace'} ne '##other'
	? '  ns(' . $$at{'namespace'} . ')' : '';
    sg_magic_newline();
    sg_out($indent . '@any' . $ns . "\n");
}

sub union {
    my ($indent) = @_;
    my $at = attrs($indent.'    ');
    if ($tok[$i] eq '>') {
	++$i;
	close_tag($indent, $tag);
    } if ($tok[$i] eq '/>') {
	++$i;
    } else {
	die "junk at end of union $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
    }
    # *** what does UNION mean? Could it be expressed using minOccurs=0 instead?
    sg_out($indent . "union($$at{'memberTypes'})\n");    
}

sub annotation {
    my ($indent) = @_;
    attrs($indent.'    ');
    ++$i;
    documentation($indent) if $tok[$i] =~ /^<(\w+:)?documentation$/;
    close_tag($indent, 'annotation');
}

sub documentation {
    my ($indent) = @_;
    attrs($indent.'    ');
    ++$i;
    # *** the text of the documentation goes here, but see
    # *** documentation elimination substitution above
    close_tag($indent, 'documentation');
}

sub sg_magic_semicolon {
    my ($indent) = @_;
    if (substr($sg,-1) eq "\n") {
	sg_out($indent."  ;\n");
    } else {
	sg_out(" ;\n");
    }
}

sub sg_magic_newline {
    if (substr($sg,-1) ne "\n") {
	sg_out("\n");
    }
}

sub element {
    my ($indent) = @_;
    my $at = attrs($indent.'    ');
    if ($tok[$i] eq '>') {
	++$i;
	if ($$at{'ref'}) {
	    sg_out($indent . $$at{'ref'} . $$at{'occurs'} . "\n");
	    annotation($indent) if $tok[$i] =~ /^<(\w+:)?annotation$/;
	} elsif ($$at{'type'}) {
	    sg_out($indent . $$at{'name'} . $$at{'occurs'} . "\t -> \%" . $$at{'type'}. "\n");
	    annotation($indent) if $tok[$i] =~ /^<(\w+:)?annotation$/;
	} else {
	    sg_out($indent . $$at{'name'} . $$at{'occurs'} . ':');
	    annotation($indent) if $tok[$i] =~ /^<(\w+:)?annotation$/;
	    complex_or_simple_type($indent.'  ');
	    sg_magic_semicolon($indent);
	}
	close_tag($indent, 'element');
    } elsif ($tok[$i] eq '/>') {
	++$i;
	if ($$at{'ref'}) {
	    sg_out($indent . $$at{'ref'} . $$at{'occurs'} . "\n");
	} elsif ($$at{'type'}) {
	    sg_out($indent . $$at{'name'} . $$at{'occurs'} . "\t -> \%" . $$at{'type'}. "\n");
	} else {
	    die "Expected ref or type XML attribute $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
	}
    } else {
	die "junk at end of element $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
    }
}

sub attribute {
    my ($indent) = @_;
    my ($use_ind);
    my $at = attrs($indent.'    ');
    sg_magic_newline();
    #warn "HERE $i: ($tok[$i-1]) ($tok[$i-1]) ($tok[$i]) ($tok[$i+1]) ($tok[$i+1])";
    if ($$at{'use'} eq 'required' || $indent eq '') {
	$use_ind = '';
    } else {
	$use_ind = '?';
    }
    
    if ($tok[$i] eq '>') {
	++$i;
	sg_out($indent . '@' . $$at{'name'} . $use_ind . ':');
	if (complex_or_simple_type($indent.'  ')) {
	    sg_magic_semicolon($indent);
	    close_tag($indent, 'attribute');
	    return;
	} else {     # Must have been just an attribute, fall thru to normal handling
	    close_tag($indent, 'attribute');
	}
    } elsif ($tok[$i] eq '/>') {
	++$i;
    } else {
	die "junk at end of attribute $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
    }
    if ($$at{'ref'}) {
	sg_out($indent . '@' . $$at{'ref'} . $use_ind . "\n");
    } else {
	my $default = defined($$at{'default'}) ? '  default (' . $$at{'default'} . ')' : '';
	sg_out($indent . '@' . $$at{'name'} . $use_ind . "\t -> \%" . $$at{'type'} . $default . "\n");
    }
}

sub complex_or_simple_type {
    my ($indent) = @_;
    while ($tok[$i] !~ /^<\//) {
      if ($tok[$i] =~ /^<(\w+:)?complexType$/) {         complexType($indent); return 1;
      } elsif ($tok[$i] =~ /^<(\w+:)?simpleType$/) {     simpleType($indent);  return 1;
      } elsif ($tok[$i] =~ /^<(\w+:)?annotation$/) {     annotation($indent);
      } else {
	die "Expected complex or simple type $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
      }
    }
    return 0;
}

sub complexType {
    my ($indent) = @_;
    my $at = attrs($indent.'    ');
    if ($tok[$i] eq '/>') {
	++$i;
	return;
    }
    
    ++$i;  # '>'
    
    if ($$at{'name'}) {
	sg_out($indent.'%'.$$at{'name'}.':');
	$indent .= '  ';
    }
    
    while ($i <= $#tok && $tok[$i] !~ m%</(\w+:)?complexType$%) {
	if ($tok[$i] =~ /^<(\w+:)?sequence$/) {            sequence($indent);
        } elsif ($tok[$i] =~ /^<(\w+:)?choice$/) {         choice($indent);
        } elsif ($tok[$i] =~ /^<(\w+:)?all$/) {            all($indent);
        } elsif ($tok[$i] =~ /^<(\w+:)?simpleContent$/) {  simpleContent($indent);
        } elsif ($tok[$i] =~ /^<(\w+:)?complexContent$/) { complexContent($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?group$/) {          group($indent);
        } elsif ($tok[$i] =~ /^<(\w+:)?attribute$/) {      attribute($indent);
        } elsif ($tok[$i] =~ /^<(\w+:)?anyAttribute$/) {   anyAttribute($indent);
        } elsif ($tok[$i] =~ /^<(\w+:)?attributeGroup$/) { attributeGroup($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?annotation$/) {     annotation($indent);
        } else { die "Expected sequence, choice, simpleContent, or attribute in complexType $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"; }
    }

    if ($$at{'name'}) {
	chop $indent;
	chop $indent;
	sg_magic_semicolon($indent);
    }

    close_tag($indent,'complexType');
}

sub simpleType {
    my ($indent) = @_;
    my $at = attrs($indent.'    ');
    ++$i;
    
    if ($$at{'name'}) {
	sg_out($indent.'%'.$$at{'name'}.':');
	$indent .= '  ';
    }
    
    while ($i <= $#tok && $tok[$i] !~ m%</(\w+:)?simpleType$%) {
	if ($tok[$i] =~ /^<(\w+:)?restriction$/) {             restriction($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?union$/) {              union($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?annotation$/) {         annotation($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?list$/) {               list($indent);
        } else { die "Expected restriction $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])"; }
    }

    if ($$at{'name'}) {
	chop $indent;
	chop $indent;
	sg_magic_semicolon($indent);
    }

    close_tag($indent,'simpleType');
}

sub group {
    my ($indent) = @_;
    my $at = attrs($indent.'    ');

    if ($tok[$i] eq '>') {
	++$i;
	sg_out($indent . '&' . $$at{'name'} . $$at{'occurs'}. ": ");
    
	if ($tok[$i] =~ /^<(\w+:)?sequence$/) {            sequence($indent.'  ');
	} elsif ($tok[$i] =~ /^<(\w+:)?choice$/) {         choice($indent.'  ');
	} else {
	    die "Expected complex or simple type $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
	}
	sg_magic_semicolon($indent);
	close_tag($indent, 'group');
    } elsif ($tok[$i] eq '/>') {
	++$i;
	sg_out($indent . '&' . $$at{'ref'} . $$at{'occurs'}. "\n");
    } else {
	die "junk at end of group $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
    }
    #warn "HERE $i: ($tok[$i-1]) ($tok[$i-1]) ($tok[$i]) ($tok[$i+1]) ($tok[$i+1])";
}

sub attributeGroup {
    my ($indent) = @_;
    my $at = attrs($indent.'    ');
    sg_magic_newline();

    if ($tok[$i] eq '>') {
	++$i;
	sg_out($indent . '&@' . $$at{'name'} . $$at{'occurs'}. ": ");

	while ($i <= $#tok && $tok[$i] !~ m%</(\w+:)?attributeGroup%) {
	    if ($tok[$i] =~ /^<(\w+:)?attributeGroup$/)    {  attributeGroup($indent.'  ');
	    } elsif ($tok[$i] =~ /^<(\w+:)?attribute$/)    {  attribute($indent.'  ');
	    } elsif ($tok[$i] =~ /^<(\w+:)?anyAttribute$/) {  anyAttribute($indent);
	    } elsif ($tok[$i] =~ /^<(\w+:)?annotation$/)   {  annotation($indent);
	    } else {
		die "Expected attribute or attributeGroup in attributeGroup $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
	    }
	}
	sg_magic_semicolon($indent);
	close_tag($indent, 'attributeGroup');
    } elsif ($tok[$i] eq '/>') {
	++$i;
	sg_out($indent . '&@' . $$at{'ref'} . $$at{'occurs'}. "\n");
    } else {
	die "junk at end of attributeGroup $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
    }    
}

sub simpleContent {
    my ($indent) = @_;
    attrs($indent.'    ');
    ++$i;

    if ($tok[$i] =~ /^<(\w+:)?extension$/) {            extension($indent);
    } elsif ($tok[$i] =~ /^<(\w+:)?restriction$/) {     restriction($indent);
    } else {
	die "Expected extension or restriction in simpleContent $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
    }
    close_tag($indent, 'simpleContent');
}

sub complexContent {
    my ($indent) = @_;
    attrs($indent.'    ');
    ++$i;

    while ($i <= $#tok && $tok[$i] !~ m%</(\w+:)?complexContent$%) {
	if ($tok[$i] =~ /^<(\w+:)?extension$/) {            extension($indent);
        } elsif ($tok[$i] =~ /^<(\w+:)?restriction$/) {     restriction($indent);
        } elsif ($tok[$i] =~ /^<(\w+:)?annotation$/) {      annotation($indent);
        } else {
	    die "Expected extension or restriction in complexContent $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
        }
    }
    close_tag($indent, 'complexContent');
}

sub all {
    my ($indent) = @_;
    sg_out(" all ");
    sequence_or_all($indent, 'all');
}

sub sequence {
    my ($indent) = @_;
    sequence_or_all($indent, 'sequence');
}

sub sequence_or_all {
    my ($indent, $close) = @_;
    attrs($indent.'    ');
    ++$i;
    sg_out("\n");

    while ($i <= $#tok && $tok[$i] !~ m%</(\w+:)?$close$%) {
	if ($tok[$i] =~ /^<(\w+:)?element$/) {              element($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?choice$/) {          choice($indent.'  ');
        } elsif ($tok[$i] =~ /^<(\w+:)?sequence$/) {        sequence($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?group$/) {           group($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?attributeGroup$/) {  attributeGroup($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?attribute$/) {       attribute($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?annotation$/) {      annotation($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?anyAttribute$/) {    anyAttribute($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?any$/) {             any($indent);
	} else {
	    die "expected element, group, or attribute in $close $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
	}
    }

    close_tag($indent, $close);
}

sub choice {
    my ($indent) = @_;
    attrs($indent.'    ');
    ++$i;
    sg_magic_newline();
    
    while ($i <= $#tok && $tok[$i] !~ m%</(\w+:)?choice%) {
	if ($tok[$i] =~ /^<(\w+:)?element$/) {              element($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?sequence$/) {        sequence($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?choice$/) {          choice($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?group$/) {           group($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?attributeGroup$/) {  attributeGroup($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?attribute$/) {       attribute($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?anyAttribute$/) {    anyAttribute($indent);
	} elsif ($tok[$i] =~ /^<(\w+:)?any$/) {             any($indent);
	} else {
	    die "expected element, group, or attribute in choice $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
	}
	sg_out(' | ');
    }
    chop $sg; chop $sg; # last superfluous pipey
    close_tag($indent, 'choice');
}

sub restriction {
    my ($indent) = @_;
    my @enums;
    my $at = attrs($indent.'    ');
    
    if ($tok[$i] eq '>') {
	++$i;
	if ($i <= $#tok && $tok[$i] !~ m%<(\w+:)?enumeration$%) {
	    sg_out("\t base(" . $$at{'base'} . ')');
	}
	while ($i <= $#tok && $tok[$i] !~ m%</(\w+:)?restriction%) {
	    if ($tok[$i] =~ /^<(\w+:)?enumeration$/) { push @enums, enumeration();
            } elsif ($tok[$i] =~ /^<(\w+:)?sequence$/) {
		sequence($indent.'  ');
	    } elsif ($tok[$i] =~ /^<(\w+:)?attributeGroup$/) {  attributeGroup($indent);
	    } elsif ($tok[$i] =~ /^<(\w+:)?attribute$/) {       attribute($indent);
	    } elsif ($tok[$i] =~ /^<(\w+:)?anyAttribute$/) {    anyAttribute($indent);
	    } elsif ($tok[$i] =~ /^<(\w+:)?maxLength$/) {       maxLength($indent);
	    } else {
		die "expected enumeration in restriction $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
	    }
	}
	if (@enums) {
	    sg_out("\t enum( " . join(' ', @enums) . ' )');
	} else {
	    #warn "restriction not enum $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
	}
	close_tag($indent, 'restriction');
    } elsif ($tok[$i] eq '/>') {
	++$i;
	sg_out("\t base(" . $$at{'base'} . ')');
    } else {
	die "junk at end of restriction $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
    }
}

sub maxLength {
    my ($indent) = @_;
    my $at = attrs($indent.'    ');
    if ($tok[$i] eq '>') {
	close_tag($indent, 'maxLength');
    } elsif ($tok[$i] eq '/>') {
	++$i;
    } else {
	die "junk at end of maxLength $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
    }
}

sub list {
    my ($indent) = @_;
    my $at = attrs($indent.'    ');
    if ($tok[$i] eq '>') {
	close_tag($indent, 'list');
    } elsif ($tok[$i] eq '/>') {
	++$i;
    } else {
	die "junk at end of list $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
    }
    sg_out("\t" . $$at{'itemType'} . "*");
}

sub extension {
    my ($indent) = @_;
    my $at = attrs($indent.'    ');

    sg_out("\t base(" . $$at{'base'} . ")");

    if ($tok[$i] eq '>') {
	++$i;
	while ($i <= $#tok && $tok[$i] !~ m%</(\w+:)?extension%) {
	    if ($tok[$i] =~ /^<(\w+:)?attributeGroup$/) {  attributeGroup($indent);
	    } elsif ($tok[$i] =~ /^<(\w+:)?attribute$/) {  attribute($indent);
            } elsif ($tok[$i] =~ /^<(\w+:)?sequence$/) {   sequence($indent.'  ');
            } elsif ($tok[$i] =~ /^<(\w+:)?choice$/) {     choice($indent.'  ');
            } elsif ($tok[$i] =~ /^<(\w+:)?anyAttribute$/) {    anyAttribute($indent);
            } else {
	       die "expected attribute or attribute group in extension $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
	    }
        }
       close_tag($indent, 'extension');
    } elsif ($tok[$i] eq '/>') {
	++$i;
    } else {
	die "junk at end of extension $i: ($tok[$i-1]) ($tok[$i]) ($tok[$i+1])";
    }
}

print $sg;

###################################################################
### Code and data type generation

sub expand_attributeGroup {
    my ($ag) = @_;
    my $i;
    for ($i = 0; $i <= $#{$dt{'attributeGroup'}{$ag}}; ++$i) {
	my ($op, $name, $n_min, $n_max) = split /\$/, $dt{'attributeGroup'}{$ag}[$i];
	next if $op eq 'attribute';
	next if $op eq 'anyAttribute';
	die "bad op($op) ($ag) ag($dt{'attributeGroup'}{$ag}[$i])" if $op ne 'attributeGroup';
	expand_attributeGroup($name);
	splice @{$dt{'attributeGroup'}{$ag}}, $i, 1, @{$dt{'attributeGroup'}{$name}};
    }
}

sub expand_group {
    my ($ag) = @_;
    my $i;
    for ($i = 0; $i <= $#{$dt{'group'}{$ag}}; ++$i) {
	my ($op, $name, $n_min, $n_max) = split /\$/, $dt{'group'}{$ag}[$i];
	if ($op eq 'complexType') {
	    expand_complexType($name);
	    splice @{$dt{'group'}{$ag}}, $i, 1, @{$dt{'complexType'}{$name}};
	}
	elsif ($op eq 'group') {
	    expand_group($name);
	    splice @{$dt{'group'}{$ag}}, $i, 1, @{$dt{'group'}{$name}};
	}
	elsif ($op eq 'attributeGroup') {
	    expand_attributeGroup($name);
	    splice @{$dt{'group'}{$ag}}, $i, 1, @{$dt{'attributeGroup'}{$name}};
	}
    }
}

sub expand_complexType {
    my ($ag) = @_;
    my $i;
    for ($i = 0; $i <= $#{$dt{'complexType'}{$ag}}; ++$i) {
	my ($op, $name, $n_min, $n_max) = split /\$/, $dt{'complexType'}{$ag}[$i];
	if ($op eq 'complexType') {
	    expand_complexType($name);
	    splice @{$dt{'complexType'}{$ag}}, $i, 1, @{$dt{'complexType'}{$name}};
	}
	elsif ($op eq 'group') {
	    expand_group($name);
	    splice @{$dt{'complexType'}{$ag}}, $i, 1, @{$dt{'group'}{$name}};
	}
	elsif ($op eq 'attributeGroup') {
	    expand_attributeGroup($name);
	    splice @{$dt{'complexType'}{$ag}}, $i, 1, @{$dt{'attributeGroup'}{$name}};
	}
    }
}

sub expand_element {
    my ($ag) = @_;
    my ($i, $el, $nam);
    ($el = $ag) =~ s/[:-]/_/g;
    #warn "expand el($ag)" . Dumper($dt{'element'}{$ag});
    if (ref $dt{'element'}{$ag} eq '') { # scalar
	my ($op, $name, $n_min, $n_max) = split /\$/, $dt{'element'}{$ag};
	if ($op eq 'ref') {
	    #warn "expand ref el($ag) name($name) cx($dt{'complexType'}{$name})";
	    $dt{'element'}{$ag} = $dt{'complexType'}{$name} if $dt{'complexType'}{$name};
	} else {
	    die "bad op($op)";
	}
    }
    
    # Now complex types and groups
    
    for ($i = 0; $i <= $#{$dt{'element'}{$ag}}; ++$i) {
	my ($op, $name, $n_min, $n_max,$type) = split /\$/, $dt{'element'}{$ag}[$i];
	($nam = $name) =~ s/[:-]/_/g;
	$refby{$nam} .= " $tx${el}_s";
	#warn "REFBY($nam): $el";
	if ($op eq 'complexType') {
	    expand_complexType($name);
	    splice @{$dt{'element'}{$ag}}, $i, 1, @{$dt{'complexType'}{$name}};
	    redo;  # recheck the expansion for complex types
	} elsif ($op eq 'group') {
	    expand_group($name);
	    splice @{$dt{'element'}{$ag}}, $i, 1, @{$dt{'group'}{$name}};
	    redo;  # recheck the expansion
	} elsif ($op eq 'attributeGroup') {
	    expand_attributeGroup($name);
	    splice @{$dt{'element'}{$ag}}, $i, 1, @{$dt{'attributeGroup'}{$name}};
	    redo;  # recheck the expansion
	} elsif ($op eq 'base') {
	    #warn "expanding base($type)";
	    splice @{$dt{'element'}{$ag}}, $i, 1, ();  # remove base indicator
	    # Add contents of base always to beginning so that types derived
	    # from same base are isomorphic wrt to the fields from the base.
	    splice @{$dt{'element'}{$ag}}, 0, 0, @{$dt{'complexType'}{$type}};
	    $i = -1;  # restart checking: base may have a base or comlpex types
	}
    }

    # *** Exclusive XML canonicalization rules dictate that attributes
    # are sorted alphabetically.
}

sub trivial_complexType_check {
    my ($name) = @_;
    if ($#{$dt{'element'}{$name}} == 0) {   # check for trivial complexType
	my ($op, $name1, undef, undef, $type) = split /\$/, $dt{'element'}{$name}[0];
	if ($op eq '_d') {
	    #warn "Collapsing trivial complexType($name) to type($type)";
	    return $type;
	} elsif ($op eq 'any') {  # ***
	} elsif ($op eq 'element') {
	    #die "*** not thought out";
	    #$dt{'element'}{$name} = $dt{'element'}{$name}[0];  # *** naming conflicts?
	} elsif ($op eq 'attribute') {
	    #die "*** not thought out";
	    #$dt{'element'}{$name} = $dt{'element'}{$name}[0];
	} elsif ($op eq 'anyAttribute') {
	} else {
	    die "Bad op($op) name($name) name1($name1)";
	}
    }
    return $undef;
}

####################################################################
### C code generation
###

sub gen_element {
    my ($el, $def, $rootp) = @_;
    my ($i, $attrs, $elems,
	$attrs_so_len, $attrs_wo_len, $elems_so_len, $elems_wo_len,
	$attrs_so_enc, $attrs_wo_enc, $elems_so_enc, #  $len_wo, $enc_wo, MUST NOT be local!
	$attrs_dup_strs, $elems_dup_strs, $attrs_free, $elems_free,
	$attrs_clone, $elems_clone,
	$xmlns_so_enc, $xmlns_so_len, $xmlns_wo_enc, $xmlns_wo_len,
	$attrs_walk_so, $elems_walk_so, $attrs_walk_wo, $elems_walk_wo,
	$getput_attr_get_hdrs, $getput_get_hdrs, $getput_num_hdrs, $getput_pop_hdrs,
	$getput_push_hdrs, $getput_attr_put_hdrs, $getput_put_hdrs,
	$getput_add_hdrs, $getput_del_hdrs, $getput_rev_hdrs, $field, $type,
	%fields, @attrs);
    return unless ref $def eq 'ARRAY';
    my ($ns, $tag) = $el =~ /^(?:(\w+):)?([\w-]+)$/;
    $el =~ s/[:-]/_/g;
    if ($ns) {
	elems_gperf_out qq($tag, "$ns", ${tx}ns_tab + ${tx}xmlns_ix_$ns\n);
    } else {
	elems_gperf_out qq($tag, "$ns", 0\n);
    }
    my @seen_ns = ($ns);
    my $n_decode = $rootp ? ', int n_decode' : '';
    hdr_out <<HDR;
/* -------------------------- $el -------------------------- */
/* refby($refby{$el} ) */
#ifndef $tx${el}_EXT
#define $tx${el}_EXT
#endif

struct $tx${el}_s* ${tx}DEC_${el}(struct ${zx}_ctx* c, struct ${zx}_ns_s* ns$n_decode);
struct $tx${el}_s* ${tx}NEW_${el}(struct ${zx}_ctx* c);
struct $tx${el}_s* ${tx}DEEP_CLONE_${el}(struct ${zx}_ctx* c, struct $tx${el}_s* x, int dup_strs);
void ${tx}DUP_STRS_${el}(struct ${zx}_ctx* c, struct $tx${el}_s* x);
void ${tx}FREE_${el}(struct ${zx}_ctx* c, struct $tx${el}_s* x, int free_strs);
int ${tx}WALK_SO_${el}(struct ${zx}_ctx* c, struct $tx${el}_s* x, void* ctx, int (*callback)(struct ${zx}_node_s* node, void* ctx));
int ${tx}WALK_WO_${el}(struct ${zx}_ctx* c, struct $tx${el}_s* x, void* ctx, int (*callback)(struct ${zx}_node_s* node, void* ctx));
int ${tx}LEN_SO_${el}(struct ${zx}_ctx* c, struct $tx${el}_s* x);
int ${tx}LEN_WO_${el}(struct ${zx}_ctx* c, struct $tx${el}_s* x);
char* ${tx}ENC_SO_${el}(struct ${zx}_ctx* c, struct $tx${el}_s* x, char* p);
char* ${tx}ENC_WO_${el}(struct ${zx}_ctx* c, struct $tx${el}_s* x, char* p);
struct ${zx}_str* ${tx}EASY_ENC_SO_${el}(struct ${zx}_ctx* c, struct $tx${el}_s* x);
struct ${zx}_str* ${tx}EASY_ENC_WO_${el}(struct ${zx}_ctx* c, struct $tx${el}_s* x);

struct $tx${el}_s {
  ${ZX}_ELEM_EXT
  $tx${el}_EXT
HDR
;
    for ($i = 0; $i <= $#{$def}; ++$i) {  # Go through lines of definition
	my ($op, $name, $n_min, $n_max, $type) = split /\$/, $$def[$i];
	my ($name_ns, $name_tag) = $name =~ /^(?:(\w+):)?([\w-]+)$/;
	my $ns_name = length($name_ns) ? "${name_ns}_${name_tag}" : $name_tag;
	if ($fields{$name_tag}) {
	    $field = $ns_name;
	    warn "Duplicate field name($name_tag) using ns prefixed field name($field)\n"
		unless $op eq 'anyAttribute';
	    #warn "-- $i: def($$def[$i]) name($name)";
	} else {
	    $field = $name_tag;
	    $fields{$field} = 1;
	}
	if ($rename{$field}) {
	    warn "Field name($field) is keyword and will be renamed to($rename{$field})\n";
	    $field = $rename{$field};
	}
	if ($op eq 'element') {
	    if (ref $dt{'element'}{$name} eq '') {  # referenced element is obvious scalar
		($op, $type) = split /\$/, $dt{'element'}{$name};
		die "bad op($op) name($name): Missing external dependency?\n-- $i: def($$def[$i])"
		    if $op ne 'ref' && $op ne 'enum' && !$ignore_ext{$name_ns};

		#
		# code for simple child elements
		#

scalar_elem:
		++$needed_elems{$name};
		$elems .= <<DEC;
          case ${tx}${ns_name}_ELEM:
            el = ${tx}DEC_simple_elem(c, ns, tok);
            el->g.n = &x->${field}->g;
            x->${field} = el;
            break;
DEC
    ;
		$elems_so_len .= <<ENC;
  for (se = x->${field}; se; se = (struct ${zx}_elem_s*)se->g.n)
    len += ${tx}LEN_SO_simple_elem(c,se, sizeof("${name}")-1, ${tx}ns_tab+${tx}xmlns_ix_$name_ns);
ENC
    ;
		$elems_wo_len .= <<ENC;
  for (se = x->${field}; se; se = (struct ${zx}_elem_s*)se->g.n)
    len += ${tx}LEN_WO_simple_elem(c, se, sizeof("${name_tag}")-1);
ENC
    ;
		$elems_so_enc .= <<ENC;
  for (se = x->${field}; se; se = (struct ${zx}_elem_s*)se->g.n)
    p = ${tx}ENC_SO_simple_elem(c, se, p, "${name}", sizeof("${name}")-1, ${tx}ns_tab+${tx}xmlns_ix_$name_ns);
ENC
    ;
		$len_wo .= <<ENC_WO unless $enc_wo_seen{$ns_name};
  case ${tx}${ns_name}_ELEM:
    return ${tx}LEN_WO_simple_elem(c, (struct ${zx}_elem_s*)x, sizeof("${name_tag}")-1);
ENC_WO
    ;
		$enc_wo .= <<ENC_WO unless $enc_wo_seen{$ns_name};
  case ${tx}${ns_name}_ELEM:
    return ${tx}ENC_WO_simple_elem(c, (struct ${zx}_elem_s*)x, p, "${name_tag}", sizeof("${name_tag}")-1);
ENC_WO
    ;
		++$enc_wo_seen{$ns_name};
		$elems_dup_strs .= "  ${zx}_dup_strs_simple_elems(c, x->$field);\n";
		$elems_free .=  "  ${zx}_free_simple_elems(c, x->$field, free_strs);\n";
		$elems_clone .= "  x->$field = ${zx}_deep_clone_simple_elems(c,x->$field, dup_strs);\n";
		$elems_walk_so .= "  ret = ${zx}_walk_so_simple_elems(c, x->$field, ctx, callback);\n";
		$elems_walk_so .= "  if (ret)\n    return ret;\n";

		$x = $getput_subtempl;
		$x =~ s/TX/$tx/g;
		$x =~ s/ELNAME/$el/g;
		$x =~ s/FNAME/$field/g;
		$x =~ s/ELTYPE/$tx$el/g;
		$x =~ s/FTYPE/${zx}_elem/g;
		$x =~ s/-\>gg\.g/-\>g/gs;
		getput_out $x;
		$getput_num_hdrs .= "int ${tx}${el}_NUM_${field}(struct $tx${el}_s* x);\n";
		$getput_get_hdrs .= "struct ${zx}_elem_s* ${tx}${el}_GET_${field}(struct $tx${el}_s* x, int n);\n";
		$getput_pop_hdrs .= "struct ${zx}_elem_s* ${tx}${el}_POP_${field}(struct $tx${el}_s* x);\n";
		$getput_push_hdrs .= "void ${tx}${el}_PUSH_${field}(struct $tx${el}_s* x, struct ${zx}_elem_s* y);\n";
		$getput_rev_hdrs .= "void ${tx}${el}_REV_${field}(struct $tx${el}_s* x);\n";
		$getput_put_hdrs .= "void ${tx}${el}_PUT_${field}(struct $tx${el}_s* x, int n, struct ${zx}_elem_s* y);\n";
		$getput_add_hdrs .= "void ${tx}${el}_ADD_${field}(struct $tx${el}_s* x, int n, struct ${zx}_elem_s* z);\n";
		$getput_del_hdrs .= "void ${tx}${el}_DEL_${field}(struct $tx${el}_s* x, int n);\n";
                hdr_out "  struct ${zx}_elem_s* ${field};\t/* {$n_min,$n_max} $type */\n";
	    } else {  # referenced element is a complex type
		if ($#{$dt{'element'}{$name}} == 0) {   # check for trivial complexType
		    $type = trivial_complexType_check($name);
		    goto scalar_elem if $type;
		}
		
		#
		# code for complex child elements
		#
		
		$elems .= <<DEC;
          case ${tx}${ns_name}_ELEM:
            el = (struct ${zx}_elem_s*)${tx}DEC_${ns_name}(c, ns);
            el->g.n = &x->${field}->gg.g;
            x->${field} = (struct $tx${ns_name}_s*)el;
            break;
DEC
    ;
		# When canonicalizing for signature verification,
		# the embedded signature is to be excluded.
		if ($ns_name eq 'ds_Signature') {
		    $elems_so_len .= <<ENC;
  {
      struct $tx${ns_name}_s* e;
      for (e = x->${field}; e; e = (struct $tx${ns_name}_s*)e->gg.g.n)
	  if (e != c->exclude_sig)
              len += ${tx}LEN_SO_${ns_name}(c, e);
  }
ENC
    ;
		    $elems_wo_len .= <<ENC;
  {
      struct $tx${ns_name}_s* e;
      for (e = x->${field}; e; e = (struct $tx${ns_name}_s*)e->gg.g.n)
	  if (e != c->exclude_sig)
              len += ${tx}LEN_WO_${ns_name}(c, e);
  }
ENC
    ;
		    $elems_so_enc .= <<ENC;
  {
      struct $tx${ns_name}_s* e;
      for (e = x->${field}; e; e = (struct $tx${ns_name}_s*)e->gg.g.n)
	  if (e != c->exclude_sig)
              p = ${tx}ENC_SO_${ns_name}(c, e, p);
  }
ENC
    ;
		    $len_wo .= <<ENC_WO unless $enc_wo_seen{$ns_name};
  case ${tx}${ns_name}_ELEM:
    return (x != c->exclude_sig) ? ${tx}LEN_WO_${ns_name}(c, (struct $tx${ns_name}_s*)x) : 0;
ENC_WO
    ;
		    $enc_wo .= <<ENC_WO unless $enc_wo_seen{$ns_name};
  case ${tx}${ns_name}_ELEM:
    return (x != c->exclude_sig) ? ${tx}ENC_WO_${ns_name}(c, (struct $tx${ns_name}_s*)x, p) : p;
ENC_WO
    ;
		} else {
		    $elems_so_len .= <<ENC;
  {
      struct $tx${ns_name}_s* e;
      for (e = x->${field}; e; e = (struct $tx${ns_name}_s*)e->gg.g.n)
	  len += ${tx}LEN_SO_${ns_name}(c, e);
  }
ENC
    ;
		    $elems_wo_len .= <<ENC;
  {
      struct $tx${ns_name}_s* e;
      for (e = x->${field}; e; e = (struct $tx${ns_name}_s*)e->gg.g.n)
	  len += ${tx}LEN_WO_${ns_name}(c, e);
  }
ENC
    ;
		    $elems_so_enc .= <<ENC;
  {
      struct $tx${ns_name}_s* e;
      for (e = x->${field}; e; e = (struct $tx${ns_name}_s*)e->gg.g.n)
	  p = ${tx}ENC_SO_${ns_name}(c, e, p);
  }
ENC
    ;
		    $len_wo .= <<ENC_WO unless $enc_wo_seen{$ns_name};
  case ${tx}${ns_name}_ELEM:
    return ${tx}LEN_WO_${ns_name}(c, (struct $tx${ns_name}_s*)x);
ENC_WO
    ;
		    $enc_wo .= <<ENC_WO unless $enc_wo_seen{$ns_name};
  case ${tx}${ns_name}_ELEM:
    return ${tx}ENC_WO_${ns_name}(c, (struct $tx${ns_name}_s*)x, p);
ENC_WO
    ;
		}
		++$enc_wo_seen{$ns_name};
		$elems_dup_strs .= <<DUP;
  {
      struct $tx${ns_name}_s* e;
      for (e = x->${field}; e; e = (struct $tx${ns_name}_s*)e->gg.g.n)
	  ${tx}DUP_STRS_${ns_name}(c, e);
  }
DUP
    ;
		$elems_free .= <<FREE;
  {
      struct $tx${ns_name}_s* e;
      struct $tx${ns_name}_s* en;
      for (e = x->${field}; e; e = en) {
	  en = (struct $tx${ns_name}_s*)e->gg.g.n;
	  ${tx}FREE_${ns_name}(c, e, free_strs);
      }
  }
FREE
    ;
		$elems_clone .= <<CLONE;
  {
      struct $tx${ns_name}_s* e;
      struct $tx${ns_name}_s* en;
      struct $tx${ns_name}_s* enn;
      for (enn = 0, e = x->${field}; e; e = (struct $tx${ns_name}_s*)e->gg.g.n) {
	  en = ${tx}DEEP_CLONE_${ns_name}(c, e, dup_strs);
	  if (!enn)
	      x->${field} = en;
	  else
	      enn->gg.g.n = &en->gg.g;
	  enn = en;
      }
  }
CLONE
    ;
		$elems_walk_so .= <<WALKSO;
  {
      struct $tx${ns_name}_s* e;
      for (e = x->${field}; e; e = (struct $tx${ns_name}_s*)e->gg.g.n) {
	  ret = ${tx}WALK_SO_${ns_name}(c, e, ctx, callback);
	  if (ret)
	      return ret;
      }
  }
WALKSO
    ;
		$x = $getput_subtempl;
		$x =~ s/TX/$tx/g;
		$x =~ s/ELNAME/$el/g;
		$x =~ s/FNAME/$field/g;
		$x =~ s/ELTYPE/$tx$el/g;
		$x =~ s/FTYPE/$tx$ns_name/g;
		getput_out $x;
		$getput_num_hdrs .=  "int ${tx}${el}_NUM_${field}(struct $tx${el}_s* x);\n";
		$getput_get_hdrs .=  "struct $tx${ns_name}_s* ${tx}${el}_GET_${field}(struct $tx${el}_s* x, int n);\n";
		$getput_pop_hdrs .=  "struct $tx${ns_name}_s* ${tx}${el}_POP_${field}(struct $tx${el}_s* x);\n";
		$getput_push_hdrs .= "void ${tx}${el}_PUSH_${field}(struct $tx${el}_s* x, struct $tx${ns_name}_s* y);\n";
		$getput_rev_hdrs .=  "void ${tx}${el}_REV_${field}(struct $tx${el}_s* x);\n";
		$getput_put_hdrs .=  "void ${tx}${el}_PUT_${field}(struct $tx${el}_s* x, int n, struct $tx${ns_name}_s* y);\n";
		$getput_add_hdrs .=  "void ${tx}${el}_ADD_${field}(struct $tx${el}_s* x, int n, struct $tx${ns_name}_s* z);\n";
		$getput_del_hdrs .=  "void ${tx}${el}_DEL_${field}(struct $tx${el}_s* x, int n);\n";
                hdr_out "  struct $tx${ns_name}_s* ${field};\t/* {$n_min,$n_max} $type */\n";
	    }

	    #
	    # code for attribute
	    #

	} elsif ($op eq 'attribute') {
	    ($op, $type) = split /\$/, $dt{'attribute'}{$name};
	    die "bad op($op) attr name($name)" if $op ne 'ref' && $op ne 'enum';
	    ++$needed_attrs{$name};
	    push @attrs, [ $ns_name, $field, $name_ns, $name_tag, $n_min, $n_max, $type ];
	} elsif ($op eq '_d') {
	    # *** ${zx}_elem_s already admits string content, how to specialize it?
	} elsif ($op eq 'any') {
	    # ***
	} elsif ($op eq 'anyAttribute') {
	    # ***
	} else {
	    die "bad op($op) name($name)";
	}
    }
  
  # Sort attributes as demanded by exc-c14n and render them: first by ns URI and
  # then by attribute name. Also, capture the namespaces so we can gen xmlns decls.

  for $attr (sort { ($ns_tab{$$a[2]} cmp $ns_tab{$$b[2]}) || ($$a[3] cmp $$b[3]) } @attrs) {
      ($ns_name, $field, $name_ns, $name_tag, $n_min, $n_max, $type) = @{$attr};
      push @seen_ns, $name_ns if length $name_ns;
      $attrs .= <<DEC;
    case ${tx}${ns_name}_ATTR:
      ss = ${ZX}_ZALLOC(c, struct ${zx}_str);
      ss->g.n = &x->${field}->g;
      x->${field} = ss;
      ${ZX}_ATTR_DEC_EXT(ss);
      break;
DEC
    ;
      $attrs_so_len   .= "  len += ${zx}_attr_so_len(x->$field, sizeof(\"$ns_name\")-1);\n";
      $attrs_wo_len   .= "  len += ${zx}_attr_wo_len(x->$field, sizeof(\"$name_tag\")-1);\n";
      $attrs_so_enc   .= qq{  p = ${zx}_attr_so_enc(p, x->$field, " $ns_name=\\"", sizeof(" $ns_name=\\"")-1);\n};
      $attrs_wo_enc   .= qq{  p = ${zx}_attr_wo_enc(p, x->$field, "$name_tag=\\"", sizeof("$name_tag=\\"")-1);\n};
      $attrs_dup_strs .= "  ${zx}_dup_attr(c, x->$field);\n";
      $attrs_free  .= "  ${zx}_free_attr(c, x->$field, free_strs);\n";
      $attrs_clone .= "  x->$field = ${zx}_clone_attr(c, x->$field);\n";
      getput_out <<GETPUT;
/* FUNC(${tx}${el}_GET_${field}) */
struct ${zx}_str* ${tx}${el}_GET_${field}(struct $tx${el}_s* x) { return x->${field}; }
/* FUNC(${tx}${el}_PUT_${field}) */
void ${tx}${el}_PUT_${field}(struct $tx${el}_s* x, struct ${zx}_str* y) { x->${field} = y; }
GETPUT
    ;
      $getput_attr_get_hdrs .= "struct ${zx}_str* ${tx}${el}_GET_${field}(struct $tx${el}_s* x);\n";
      $getput_attr_put_hdrs .= "void ${tx}${el}_PUT_${field}(struct $tx${el}_s* x, struct ${zx}_str* y);\n";
      hdr_out "  struct ${zx}_str* ${field};\t/* {$n_min,$n_max} attribute $type */\n";
  }
  
  hdr_out "};\n\n$getput_attr_get_hdrs$getput_get_hdrs$getput_num_hdrs$getput_pop_hdrs$getput_push_hdrs$getput_attr_put_hdrs$getput_put_hdrs$getput_add_hdrs$getput_del_hdrs$getput_rev_hdrs\n";
  
  $x = $dec_templ;
  $x =~ s/ATTRS;/$attrs/g;
  $x =~ s/ELEMS;/$elems/g;
  $x =~ s/ELSTRUCT/$tx${el}_s/g;
  $x =~ s/TX/$tx/g;
  $x =~ s/ELNAME/$el/g;
  $x =~ s/ELNS/$ns/g;
  $x =~ s/ELTAG/$tag/g;
  if ($rootp) {   # special processing for root node
      $x =~ s%\#if 1 /\* NORMALMODE \*/.*\#endif%%gs;
      $x =~ s/ROOT_N_DECODE/, int n_decode/g;
      $x =~ s/ROOT_CHECK_N_DECODED;/if (--n_decode) { x->gg.g.tok = tok; return x; }/g;
  } else {
      $x =~ s/ROOT_N_DECODE//g;
      $x =~ s/ROOT_CHECK_N_DECODED;//g;
  }
  dec_out $x;
  
  # XMLNS checks. Sort by ns prefix (unlike attributes which use URI).

  for $ns (sort @seen_ns) {
      $xmlns_so_len .= qq{  len += ${zx}_len_xmlns_if_not_seen(c, ${tx}ns_tab+${tx}xmlns_ix_$ns, &pop_seen);\n};
      $xmlns_wo_len .= qq{  len += ${zx}_len_xmlns_if_not_seen(c, x->gg.g.ns, &pop_seen);\n};
      $xmlns_so_enc .= qq{  p = ${zx}_enc_xmlns_if_not_seen(c, p, ${tx}ns_tab+${tx}xmlns_ix_$ns, &pop_seen);\n};
      $xmlns_wo_enc .= qq{  ${zx}_add_xmlns_if_not_seen(c, x->gg.g.ns, &pop_seen);\n};
  }
  
  $x = $enc_templ;
  $x =~ s/SIMPLELENNSARG//g;
  $x =~ s/SIMPLELENARG//g;
  $x =~ s/SIMPLETAGLENNSARG//g;
  $x =~ s/SIMPLETAGLENARG//g;
  $x =~ s/SIMPLELENNS//g;
  $x =~ s/SIMPLELEN//g;
  $x =~ s/SIMPLETAGLENNS//g;  
  $x =~ s/SIMPLETAGLEN//g;  
  $x =~ s/XMLNS_SO_LEN;/$xmlns_so_len/g;
  $x =~ s/XMLNS_WO_LEN;/$xmlns_wo_len/g;
  $x =~ s/ATTRS_SO_LEN;/$attrs_so_len/g;
  $x =~ s/ATTRS_WO_LEN;/$attrs_wo_len/g;
  $x =~ s/ELEMS_SO_LEN;/$elems_so_len/g;
  $x =~ s/ELEMS_WO_LEN;/$elems_wo_len/g;
  $x =~ s/XMLNS_SO_ENC;/$xmlns_so_enc/g;
  $x =~ s/XMLNS_WO_ENC;/$xmlns_wo_enc/g;
  $x =~ s/ATTRS_SO_ENC;/$attrs_so_enc/g;
  $x =~ s/ATTRS_WO_ENC;/$attrs_wo_enc/g;
  $x =~ s/ELEMS_SO_ENC;/$elems_so_enc/g;
  #$x =~ s/ANYELEM_WO_ENC;/$elems_wo_enc/g;
  $x =~ s/ELSTRUCT/$tx${el}_s/g;
  $x =~ s/TX/$tx/g;
  $x =~ s/ELNAME/$el/g;
  $x =~ s/ELNSC/$ns:/g;
  $x =~ s/ELNS/$ns/g;
  $x =~ s/ELTAG/$tag/g;
  if ($rootp) {   # special processing for root node
      $x =~ s%\#if 1 /\* NORMALMODE \*/.*?\#else(.*?)\#endif%$1%gs;
  }
  enc_out $x;
  
  $x = $aux_templ;
  $x =~ s/ATTRS_WALK_SO;/$attrs_walk_so/g;
  $x =~ s/ELEMS_WALK_SO;/$elems_walk_so/g;
  $x =~ s/ATTRS_WALK_WO;/$attrs_walk_wo/g;
  $x =~ s/ELEMS_WALK_WO;/$elems_walk_wo/g;
  $x =~ s/ATTRS_DUP_STRS;/$attrs_dup_strs/g;
  $x =~ s/ELEMS_DUP_STRS;/$elems_dup_strs/g;
  $x =~ s/ATTRS_FREE;/$attrs_free/g;
  $x =~ s/ELEMS_FREE;/$elems_free/g;
  $x =~ s/ATTRS_CLONE;/$attrs_clone/g;
  $x =~ s/ELEMS_CLONE;/$elems_clone/g;
  $x =~ s/ELSTRUCT/$tx${el}_s/g;
  $x =~ s/TX/$tx/g;
  $x =~ s/ELNAME/$el/g;
  $x =~ s/ELNS/$ns/g;
  $x =~ s/ELTAG/$tag/g;
  if ($rootp) {   # special processing for root node
      $x =~ s%\#if 1 /\* NORMALMODE \*/.*?\#else(.*?)\#endif%$1%gs;
  }
  aux_out $x;
  
  $x = $getput_templ;
  $x =~ s/ELSTRUCT/$tx${el}_s/g;
  $x =~ s/TX/$tx/g;
  $x =~ s/ELNAME/$el/g;
  $x =~ s/ELNS/$ns/g;
  $x =~ s/ELTAG/$tag/g;
  if ($rootp) {   # special processing for root node
      $x =~ s%\#if 1 /\* NORMALMODE \*/.*?\#else(.*?)\#endif%$1%gs;
  }
  getput_out $x;
}

sub read_template {
    my ($name) =@_;
    open F, "<$name-templ.c" or die "Cant read decoding template($name-templ.c)";
    $templ = <F>;
    close F;
    $templ =~ s%(/\*\*.*?\*\*/)%%s;
    ($comment) = $1;
    $templ =~ s%(/\* EOF \*/)%%s;
    $comment =~ s/\$Id/Id/;   # Preserve original CVS id
    return ($templ, $comment);
}

###
### Per namespace code generator
###

sub generate_ns {
    my ($ns, @els) = @_;
    my ($el);
    reset_accumulators();
    for $el (@els) {
	next if trivial_complexType_check($el);
	gen_element($el, $dt{'element'}{$el}, 0);
    }
    
# ==========================================================================
# Encoder
#
    
    open F, ">$gen_prefix-$ns-enc.c" or die "Cant write enc($gen_prefix-$ns-enc.c): $!";
    print F <<ENC;
/* $gen_prefix-$ns-enc.c - WARNING: This file was automatically generated. DO NOT EDIT!
 * \$Id\$ */
$copyright_msg
$enc_templ_comment

#include <memory.h>
#include "errmac.h"
#include "${zx}.h"
#include "$gen_prefix-const.h"
#include "$gen_prefix-data.h"
#include "$gen_prefix-$ns-data.h"
#include "$gen_prefix-ns.h"

ENC
    ;
    print F $enc;
    print F "/* EOF -- $gen_prefix-$ns-enc.c */\n";
    close F;
    
# ==========================================================================
# Decoder
#
    
    open F, ">$gen_prefix-$ns-dec.c" or die "Cant write dec($gen_prefix-$ns-dec.c): $!";
    print F <<DEC;
/* $gen_prefix-$ns-dec.c - WARNING: This file was automatically generated. DO NOT EDIT!
 * \$Id\$ */
$copyright_msg
$dec_templ_comment

#include "errmac.h"
#include "${zx}.h"
#include "$gen_prefix-const.h"
#include "$gen_prefix-data.h"
#include "$gen_prefix-$ns-data.h"

#define TPF $tx

#ifndef ${ZX}_ATTR_DEC_EXT
#define ${ZX}_ATTR_DEC_EXT(ss)  /* Extension point called just after decoding known attribute */
#endif

#ifndef ${ZX}_XMLNS_DEC_EXT
#define ${ZX}_XMLNS_DEC_EXT(ss) /* Extension point called just after decoding xmlns attribute */
#endif

#ifndef ${ZX}_UNKNOWN_ATTR_DEC_EXT
#define ${ZX}_UNKNOWN_ATTR_DEC_EXT(ss) /* Extension point called just after decoding unknown attr */
#endif

#ifndef ${ZX}_START_DEC_EXT
#define ${ZX}_START_DEC_EXT(x) /* Extension point called just after decoding element name and allocating struct, but before decoding any of the attributes. */
#endif

#ifndef ${ZX}_END_DEC_EXT
#define ${ZX}_END_DEC_EXT(x) /* Extension point called just after decoding the entire element. */
#endif

#ifndef ${ZX}_START_BODY_DEC_EXT
#define ${ZX}_START_BODY_DEC_EXT(x) /* Extension point called just after decoding element tag, including attributes, but before decoding the body of the element. */
#endif

#ifndef ${ZX}_PI_DEC_EXT
#define ${ZX}_PI_DEC_EXT(pi) /* Extension point called just after decoding processing instruction */
#endif

#ifndef ${ZX}_COMMENT_DEC_EXT
#define ${ZX}_COMMENT_DEC_EXT(comment) /* Extension point called just after decoding comment */
#endif

#ifndef ${ZX}_CONTENT_DEC
#define ${ZX}_CONTENT_DEC(ss) /* Extension point called just after decoding string content */
#endif

#ifndef ${ZX}_UNKNOWN_ELEM_DEC_EXT
#define ${ZX}_UNKNOWN_ELEM_DEC_EXT(elem) /* Extension point called just after decoding unknown element */
#endif

DEC
    ;
    print F $dec;
    print F "/* EOF -- $gen_prefix-$ns-dec.c */\n";
    close F;

# ==========================================================================
# Auxiliary functions: cloning, freeing, walking data structures
#
        
    open F, ">$gen_prefix-$ns-aux.c" or die "Cant write aux($gen_prefix-$ns-aux.c): $!";
    print F <<ENC;
/* $gen_prefix-$ns-aux.c - WARNING: This file was automatically generated. DO NOT EDIT!
 * \$Id\$ */
$copyright_msg
$aux_templ_comment

#include <memory.h>
#include "errmac.h"
#include "${zx}.h"
#include "$gen_prefix-const.h"
#include "$gen_prefix-data.h"
#include "$gen_prefix-$ns-data.h"

ENC
    ;
    print F $aux;
    print F "/* EOF -- $gen_prefix-$ns-aux.c */\n";
    close F;

# ==========================================================================
# GetPut: Accessor functions for struct fields
#
    
    open F, ">$gen_prefix-$ns-getput.c" or die "Cant write getput($gen_prefix-$ns--getput.c): $!";
    print F <<ENC;
/* $gen_prefix-$ns-getput.c - WARNING: This file was automatically generated. DO NOT EDIT!
 * \$Id\$ */
$copyright_msg
$getput_templ_comment

#include <memory.h>
#include "errmac.h"
#include "${zx}.h"
#include "$gen_prefix-const.h"
#include "$gen_prefix-data.h"
#include "$gen_prefix-$ns-data.h"

ENC
    ;
    print F $getput;
    print F "/* EOF -- $gen_prefix-$ns-getput.c */\n";
    close F;
    
# ==========================================================================
# Data structure definitions
#
    
    open F, ">$gen_prefix-$ns-data.h" or die "Cant write hdr($gen_prefix-$ns-data.h): $!";
    ($fold = $gen_prefix) =~ s/\W/_/gs;
    print F <<HDR;
/* $gen_prefix-$ns-data.h - WARNING: This header was automatically generated. DO NOT EDIT!
 * \$Id\$ */
/* Datastructure design, topography, and layout
 * Copyright (c) 2006 $copyright_holder,
 * All Rights Reserved. NO WARRANTY. See file COPYING for
 * terms and conditions of use. Element and attributes names as well
 * as some topography are derived from schema descriptions that were used as
 * input and may be subject to their own copright. */

#ifndef _${fold}_${ns}_data_h
#define _${fold}_${ns}_data_h

#include "${zx}.h"
#include "$gen_prefix-const.h"
#include "$gen_prefix-data.h"

#ifndef ${ZX}_ELEM_EXT
#define ${ZX}_ELEM_EXT  /* This extension point should be defined by who includes this file. */
#endif

HDR
    ;
    print F $hdr;
    print F "\n#endif\n";
    close F;
    inc_out qq(#include "$gen_prefix-$ns-data.h"\n);
}

############################################################################
### Main code generator
###

sub generate {
    #warn Dumper(\%dt);
    ($dec_templ, $dec_templ_comment) = read_template('dec');
    ($dec_lookup_subtempl) =
	$dec_templ =~ m%\#if 1 /\* DEC_LOOKUP_SUBTEMPL \*/(.*)\#endif%s;
    $dec_templ     =~ s%\#if 1 /\* DEC_LOOKUP_SUBTEMPL \*/.*\#endif%%s;
    ($enc_templ, $enc_templ_comment) = read_template('enc');
    ($enc_wo_subtempl) =
	$enc_templ =~ m%\#if 1 /\* ENC_WO_SUBTEMPL \*/(.*)\#endif%s;
    $enc_templ     =~ s%\#if 1 /\* ENC_WO_SUBTEMPL \*/.*\#endif%%s;
    ($aux_templ, $aux_templ_comment) = read_template('aux');
    ($getput_templ, $getput_templ_comment) = read_template('getput');
    ($getput_subtempl) =  # subtemplate for code that repeats per element per field
	$getput_templ =~ m%\#if 1 /\* GETPUT_SUBTEMPL \*/(.*)\#endif%s;
    $getput_templ     =~ s%\#if 1 /\* GETPUT_SUBTEMPL \*/.*\#endif%%s;
    
    @els = keys %{$dt{'element'}};
    for $k (@els) {   # Recursive expansion
	expand_element($k);
    }
    warn Dumper(\%dt) if $trace;

    for $ns (sort keys %ns_tab) {
	warn "Generating code for ns($ns)...\n" if $trace;
	generate_ns($ns, sort grep /^${ns}:/, @els);
    }
    reset_accumulators();
    warn "Generating common code...\n" if $trace;
    
    for $k (sort keys %needed_elems) {
	($ns, $tag) = $k =~ /^(?:(\w+):)?([\w-]+)$/;
	$k =~ s/[:-]/_/g;
	if ($ns) {
	    elems_gperf_out qq($tag, "$ns", ${tx}ns_tab + ${tx}xmlns_ix_$ns\n);
	} else {
	    elems_gperf_out qq($tag, "", 0\n);
	}
    }
    
    for $k (sort keys %needed_attrs) {
	($ns, $tag) = $k =~ /^(?:(\w+):)?([\w-]+)$/;
	$k =~ s/[:-]/_/g;
	if ($ns) {
	    attrs_gperf_out qq($tag, "$ns", ${tx}ns_tab + ${tx}xmlns_ix_$ns\n);
	} else {
	    attrs_gperf_out qq($tag, "", 0\n);
	}
    }
    
    @root_list = ();
    for $r (@roots) {
	push @root_list, "element\$$r\$0\$-1\$root";
	#$r =~ s/[:-]/_/g;
    }
    gen_element('root', \@root_list, 1);
        
# ==========================================================================
# Encoder
#
    
    # Generate the ${zx}_len_simple_elem(), etc., functions
    $x = $enc_templ;
    $x =~ s/XMLNS_SO_LEN;/  len += ${zx}_len_xmlns_if_not_seen(c, ns, &pop_seen);\n/g;
    $x =~ s/XMLNS_WO_LEN;/  len += ${zx}_len_xmlns_if_not_seen(c, x->gg.g.ns, &pop_seen);\n/g;
    $x =~ s/ATTRS_SO_LEN;//g;
    $x =~ s/ATTRS_WO_LEN;//g;
    $x =~ s/ELEMS_SO_LEN;//g;
    $x =~ s/ELEMS_WO_LEN;//g;
    $x =~ s/XMLNS_SO_ENC;/  p = ${zx}_enc_xmlns_if_not_seen(c, p, ns, &pop_seen);/g;
    $x =~ s/XMLNS_WO_ENC;/  ${zx}_add_xmlns_if_not_seen(c, x->gg.g.ns, &pop_seen);\n/g;
    $x =~ s/ATTRS_SO_ENC;//g;
    $x =~ s/ATTRS_WO_ENC;//g;
    $x =~ s/ELEMS_SO_ENC;//g;
    #$x =~ s/ANYELEM_WO_ENC;//g;
    $x =~ s/SIMPLELENNSARG/, simplelen, ns/g;
    $x =~ s/SIMPLELENARG/, simplelen/g;
    $x =~ s/SIMPLETAGLENNSARG/, simpletag, simplelen, ns/g;
    $x =~ s/SIMPLETAGLENARG/, simpletag, simplelen/g;
    $x =~ s/SIMPLELENNS/, int simplelen, struct ${zx}_ns_s* ns/g;
    $x =~ s/SIMPLELEN/, int simplelen/g;
    $x =~ s/SIMPLETAGLENNS/, char* simpletag, int simplelen, struct ${zx}_ns_s* ns/g;
    $x =~ s/SIMPLETAGLEN/, char* simpletag, int simplelen/g;
    $x =~ s/sizeof\("<ELNSCELTAG"\)-1/simplelen + 1/g;
    $x =~ s%sizeof\("</ELNSCELTAG>"\)-1%simplelen + 3%g;
    $x =~ s/sizeof\("ELTAG"\)-1/simplelen/g;
    $x =~ s/${ZX}_OUT_TAG\(p, "<ELNSCELTAG"\)/${ZX}_OUT_SIMPLE_TAG(p, simpletag, simplelen)/go;
    $x =~ s%${ZX}_OUT_CLOSE_TAG\(p, "</ELNSCELTAG>"\)%${ZX}_OUT_SIMPLE_CLOSE_TAG(p, simpletag,simplelen)%go;
    $x =~ s/"ELTAG"/simpletag/g;
    $x =~ s/ELSTRUCT/${zx}_elem_s/g;
    $x =~ s/TX/$tx/g;
    $x =~ s/ELNAME/simple_elem/g;
    $x =~ s/ELNSC//g;
    $x =~ s/ELNS//g;
    $x =~ s/ELTAG/simple_elem/g;
    $x =~ s/x-\>gg\./x-\>/gs;
    $x =~ s/\&x-\>gg/x/gs;
    enc_out $x;
    hdr_out <<HDR;
int ${tx}LEN_SO_simple_elem(struct ${zx}_ctx* c, struct ${zx}_elem_s* x, int simplelen, struct ${zx}_ns_s* ns);
int ${tx}LEN_WO_simple_elem(struct ${zx}_ctx* c, struct ${zx}_elem_s* x, int simplelen);
char* ${tx}ENC_SO_simple_elem(struct ${zx}_ctx* c, struct ${zx}_elem_s* x, char* p, char* simpletag, int simplelen, struct ${zx}_ns_s* ns);
char* ${tx}ENC_WO_simple_elem(struct ${zx}_ctx* c, struct ${zx}_elem_s* x, char* p, char* simpletag, int simplelen);
struct ${zx}_str* ${tx}EASY_ENC_SO_simple_elem(struct ${zx}_ctx* c, struct ${zx}_elem_s* x, char* simpletag, int simplelen, struct ${zx}_ns_s* ns);
struct ${zx}_str* ${tx}EASY_ENC_WO_simple_elem(struct ${zx}_ctx* c, struct ${zx}_elem_s* x, char* simpletag, int simplelen);
int ${tx}LEN_WO_any_elem(struct ${zx}_ctx* c, struct ${zx}_elem_s* x);
char* ${tx}ENC_WO_any_elem(struct ${zx}_ctx* c, struct ${zx}_elem_s* x, char* p);
struct ${zx}_str* ${tx}EASY_ENC_WO_any_elem(struct ${zx}_ctx* c, struct ${zx}_elem_s* x);
HDR
    ;
    
    open F, ">$gen_prefix-enc.c" or die "Cant write enc($gen_prefix-enc.c): $!";
    print F <<ENC;
/* $gen_prefix-enc.c - WARNING: This file was automatically generated. DO NOT EDIT!
 * \$Id\$ */
$copyright_msg
$enc_templ_comment

#include <memory.h>
#include "errmac.h"
#include "${zx}.h"
#include "$gen_prefix-const.h"
#include "$gen_prefix-data.h"
#include "$gen_prefix-ns.h"

ENC
    ;
    print F $enc;

    $x = $enc_wo_subtempl;
    $x =~ s/TX/$tx/g;
    $x =~ s/ANYELEM_WO_LEN;/$len_wo/g;
    $x =~ s/ANYELEM_WO_ENC;/$enc_wo/g;
    print F $x;
    print F "/* EOF -- $gen_prefix-enc.c */\n";
    close F;
    
# ==========================================================================
# Decoder
#
    
    # Generate the ${zx}_dec_simple_elem() function
    $x = $dec_templ;
    $x =~ s/ROOT_N_DECODE/, int toke/gs;
    $x =~ s/TXELNAME_ELEM/toke/gs;
    $x =~ s/ATTRS;//g;
    $x =~ s/ELEMS;//g;
    $x =~ s/ELSTRUCT/${zx}_elem_s/g;
    $x =~ s/TX/$tx/g;
    $x =~ s/ELNAME/simple_elem/g;
    $x =~ s/ELNS//g;
    $x =~ s/ELTAG/simple_elem/g;
    $x =~ s/x-\>gg\./x-\>/gs;
    $x =~ s/ROOT_CHECK_N_DECODED;//g;
    dec_out $x;
    hdr_out "struct ${zx}_elem_s* ${tx}DEC_simple_elem(struct ${zx}_ctx* c, struct ${zx}_ns_s* ns, int tok);\n";
    
    # Generate the ${zx}_dec_wrong_elem() function
    $x = $dec_templ;
    $x =~ s/ROOT_N_DECODE/, char* nam, int namlen/gs;
    $x =~ s/x-\>gg\.g\.tok = TXELNAME_ELEM;/x->gg.g.tok = ${ZX}_TOK_NOT_FOUND;/gs;
    $x =~ s/TXELNAME_ELEM/${ZX}_TOK_NOT_FOUND/gs;
    $x =~ s/ATTRS;//g;
    $x =~ s/ELEMS;//g;
    $x =~ s/ELSTRUCT/${zx}_any_elem_s/g;
    $x =~ s/TX/$tx/g;
    $x =~ s/ELNAME/wrong_elem/g;
    $x =~ s/ELNS//g;
    $x =~ s/ELTAG/wrong_elem/g;
    $x =~ s/x-\>gg\.g\.ns = .*?elems\[${ZX}_TOK_NOT_FOUND\]\.ns;/${zx}_fix_any_elem_dec(c,x,nam,namlen);/gso;
    $x =~ s///g;
    $x =~ s/ROOT_CHECK_N_DECODED;//g;
    dec_out $x;
    hdr_out "struct ${zx}_any_elem_s* ${tx}DEC_wrong_elem(struct ${zx}_ctx* c, struct ${zx}_ns_s* ns, char* nam, int namlen);\n";

    open F, ">$gen_prefix-dec.c" or die "Cant write dec($gen_prefix-dec.c): $!";
    print F <<DEC;
/* $gen_prefix-dec.c - WARNING: This file was automatically generated. DO NOT EDIT!
 * \$Id\$ */
$copyright_msg
$dec_templ_comment

#include "errmac.h"
#include "${zx}.h"
#include "$gen_prefix-const.h"
#include "$gen_prefix-data.h"

#define TPF $tx

#ifndef ${ZX}_ATTR_DEC_EXT
#define ${ZX}_ATTR_DEC_EXT(ss)  /* Extension point called just after decoding known attribute */
#endif

#ifndef ${ZX}_XMLNS_DEC_EXT
#define ${ZX}_XMLNS_DEC_EXT(ss) /* Extension point called just after decoding xmlns attribute */
#endif

#ifndef ${ZX}_UNKNOWN_ATTR_DEC_EXT
#define ${ZX}_UNKNOWN_ATTR_DEC_EXT(ss) /* Extension point called just after decoding unknown attr */
#endif

#ifndef ${ZX}_START_DEC_EXT
#define ${ZX}_START_DEC_EXT(x) /* Extension point called just after decoding element name and allocating struct, but before decoding any of the attributes. */
#endif

#ifndef ${ZX}_END_DEC_EXT
#define ${ZX}_END_DEC_EXT(x) /* Extension point called just after decoding the entire element. */
#endif

#ifndef ${ZX}_START_BODY_DEC_EXT
#define ${ZX}_START_BODY_DEC_EXT(x) /* Extension point called just after decoding element tag, including attributes, but before decoding the body of the element. */
#endif

#ifndef ${ZX}_PI_DEC_EXT
#define ${ZX}_PI_DEC_EXT(pi) /* Extension point called just after decoding processing instruction */
#endif

#ifndef ${ZX}_COMMENT_DEC_EXT
#define ${ZX}_COMMENT_DEC_EXT(comment) /* Extension point called just after decoding comment */
#endif

#ifndef ${ZX}_CONTENT_DEC
#define ${ZX}_CONTENT_DEC(ss) /* Extension point called just after decoding string content */
#endif

#ifndef ${ZX}_UNKNOWN_ELEM_DEC_EXT
#define ${ZX}_UNKNOWN_ELEM_DEC_EXT(elem) /* Extension point called just after decoding unknown element */
#endif

DEC
    ;
    print F $dec;
    $x = $dec_lookup_subtempl;
    $x =~ s/TX/$tx/g;
    print F $x;
    print F "/* EOF -- $gen_prefix-dec.c */\n";
    close F;

# ==========================================================================
# Auxiliary functions template: cloning, freeing, walking data structures
#
    
    $x = $aux_templ;
    $x =~ s/\(struct ${zx}_ctx\* c\)/(struct ${zx}_ctx* c, int toke)/gso;
    $x =~ s/TXELNAME_ELEM/toke/gs;
    $x =~ s/ATTRS_WALK_SO;//g;
    $x =~ s/ELEMS_WALK_SO;//g;
    $x =~ s/ATTRS_WALK_WO;//g;
    $x =~ s/ELEMS_WALK_WO;//g;
    $x =~ s/ATTRS_DUP_STRS;//g;
    $x =~ s/ELEMS_DUP_STRS;//g;
    $x =~ s/ATTRS_FREE;//g;
    $x =~ s/ELEMS_FREE;//g;
    $x =~ s/ATTRS_CLONE;//g;
    $x =~ s/ELEMS_CLONE;//g;
    $x =~ s/ELSTRUCT/${zx}_elem_s/g;
    $x =~ s/TX/$tx/g;
    $x =~ s/ELNAME/simple_elem/g;
    $x =~ s/ELNS//g;
    $x =~ s/ELTAG/simple_elem/g;
    $x =~ s/x-\>gg\./x-\>/gs;
    $x =~ s/\&x-\>gg/x/gs;
    aux_out $x;
    hdr_out <<HDR;
void ${tx}DUP_STRS_simple_elem(struct ${zx}_ctx* c, struct ${zx}_elem_s* x);
struct ${zx}_elem_s* ${tx}DEEP_CLONE_simple_elem(struct ${zx}_ctx* c, struct ${zx}_elem_s* x, int dup_strs);
void ${tx}FREE_simple_elem(struct ${zx}_ctx* c, struct ${zx}_elem_s* x, int free_strs);
int ${tx}WALK_SO_simple_elem(struct ${zx}_ctx* c, struct ${zx}_elem_s* x, void* ctx, int (*callback)(struct ${zx}_node_s* node, void* ctx));
int ${tx}WALK_WO_simple_elem(struct ${zx}_ctx* c, struct ${zx}_elem_s* x, void* ctx, int (*callback)(struct ${zx}_node_s* node, void* ctx));
HDR
    ;
    
    open F, ">$gen_prefix-aux.c" or die "Cant write enc($gen_prefix-aux.c): $!";
    print F <<ENC;
/* $gen_prefix-aux.c - WARNING: This file was automatically generated. DO NOT EDIT!
 * \$Id\$ */
$copyright_msg
$aux_templ_comment

#include <memory.h>
#include "errmac.h"
#include "${zx}.h"
#include "$gen_prefix-const.h"
#include "$gen_prefix-data.h"

ENC
    ;
    print F $aux;
    print F "/* EOF -- $gen_prefix-aux.c */\n";
    close F;

# ==========================================================================
# GetPut: Accessor functions for struct fields
#
    
    $x = $getput_templ;
    $x =~ s/ELSTRUCT/${zx}_elem_s/g;
    $x =~ s/TX/$tx/g;
    $x =~ s/ELNAME/simple_elem/g;
    $x =~ s/ELNS//g;
    $x =~ s/ELTAG/simple_elem/g;
    $x =~ s/x-\>gg\./x-\>/gs;
    getput_out $x;
    
    open F, ">$gen_prefix-getput.c" or die "Cant write enc($gen_prefix-getput.c): $!";
    print F <<ENC;
/* $gen_prefix-getput.c - WARNING: This file was automatically generated. DO NOT EDIT!
 * \$Id\$ */
$copyright_msg
$getput_templ_comment

#include <memory.h>
#include "errmac.h"
#include "${zx}.h"
#include "$gen_prefix-const.h"
#include "$gen_prefix-data.h"

ENC
    ;
    print F $getput;
    print F "/* EOF -- $gen_prefix-getput.c */\n";
    close F;
    
# ==========================================================================
# Data structure definitions
#
    
    open F, ">$gen_prefix-data.h" or die "Cant write hdr($gen_prefix-data.h): $!";
    ($fold = $gen_prefix) =~ s/\W/_/gs;
    print F <<HDR;
/* $gen_prefix-data.h - WARNING: This header was automatically generated. DO NOT EDIT!
 * \$Id\$ */
/* Datastructure design, topography, and layout
 * Copyright (c) 2006 $copyright_holder,
 * All Rights Reserved. NO WARRANTY. See file COPYING for
 * terms and conditions of use. Element and attributes names as well
 * as some topography are derived from schema descriptions that were used as
 * input and may be subject to their own copright. */

#ifndef _${fold}_data_h
#define _${fold}_data_h

#include "${zx}.h"
#include "$gen_prefix-const.h"
$inc

#ifndef ${ZX}_ELEM_EXT
#define ${ZX}_ELEM_EXT  /* This extension point should be defined by who includes this file. */
#endif

extern const struct ${zx}_tok ${tx}attrs[${tx}_ATTR_MAX];    /* gperf generated, see *-attrs.c */
extern const struct ${zx}_tok ${tx}elems[${tx}_ELEM_MAX];    /* gperf generated, see *-elems.c */
const struct ${zx}_tok* ${tx}attr2tok(const char* s, unsigned int len);
const struct ${zx}_tok* ${tx}elem2tok(const char* s, unsigned int len);
int ${tx}attr_lookup(struct ${zx}_ctx* c, char* name, char* lim, struct ${zx}_ns_s** ns);
int ${tx}elem_lookup(struct ${zx}_ctx* c, char* name, char* lim, struct ${zx}_ns_s** ns);

HDR
    ;
    print F $hdr;
    print F "\n#endif\n";
    close F;

# ==========================================================================
# Element table
#
    
    open F, ">$gen_prefix-elems.gperf" or die "Cant write gperf($gen_prefix-elems.gperf): $!";
    print F <<GPERF;
%{
/* $gen_prefix-elems.gperf - WARNING: This file was automatically generated. DO NOT EDIT!
 * \$Id\$ */
#include "${zx}.h"
#include "$gen_prefix-ns.h"
#include <string.h>
%}
struct ${zx}_tok { const char* name; const char* prefix; struct ${zx}_ns_s* ns; };
%%
GPERF
;
    elems_gperf_out qq("TOK_NOT_FOUND", "${ZX}", 0\n);
    print F $elems_gperf;
    print F "%%\n/* EOF - gperf -t -D -C -N${tx}elem2tok $gen_prefix-elems.gperf */\n";
    close F;

# ==========================================================================
# Attribute table
#
    
    open F, ">$gen_prefix-attrs.gperf" or die "Cant write gperf($gen_prefix-attrs.gperf): $!";
    print F <<GPERF;
%{
/* $gen_prefix-attrs.gperf - WARNING: This file was automatically generated. DO NOT EDIT!
 * \$Id\$ */
#include "${zx}.h"
#include "$gen_prefix-ns.h"
#include <string.h>
%}
struct ${zx}_tok { const char* name; const char* prefix; struct ${zx}_ns_s* ns; };
%%
xmlns, "", 0
GPERF
;
    attrs_gperf_out qq("TOK_NOT_FOUND", "${ZX}", 0\n);
    print F $attrs_gperf;
    print F "%%\n/* EOF - gperf -t -D -C -N${tx}attr2tok $gen_prefix-attrs.gperf */\n";
    close F;

# ==========================================================================
# Namespace table
#

    ++$ns_siz;  # Trailer
    open F, ">$gen_prefix-ns.c" or die "Cant write nsc($gen_prefix-ns.c): $!";
    print F <<NSOUT;
/* ${gen_prefix}-ns.c - WARNING: This file was automatically generated. DO NOT EDIT!
 * \$Id\$ */
#include "$gen_prefix-ns.h"

struct ${zx}_ns_s ${tx}ns_tab[$ns_siz] = {
$nsout
  { 0,0,0,0,0,0, 0,0,0,0 }  /* Trailer element serves at runtime to hold list of unrecognized namespaces. */
};

/* EOF */
NSOUT
;
    close F;

    open F, ">$gen_prefix-ns.h" or die "Cant write nsh($gen_prefix-ns.h): $!";
    print F <<NSOUT;
/* $gen_prefix-ns.h - WARNING: This file was automatically generated. DO NOT EDIT!
 * \$Id\$ */

#ifndef _${tx}_ns_h
#define _${tx}_ns_h
#include "${zx}.h"

extern struct ${zx}_ns_s ${tx}ns_tab[$ns_siz];

$nshout

#endif
NSOUT
;
    close F;
}

#EOF

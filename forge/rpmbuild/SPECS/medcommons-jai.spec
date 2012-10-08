Name:		medcommons-jai
Version:	1.1.3
Release:	1
Summary:	MedCommons JAI

Group:		MedCommons
License:	Sun Microsystems Binary Code License

Source0:	%{name}-%{version}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}
BuildArch:	i586

Requires:	jdk

%description
Sun's JAI packaged for the MedCommons appliance.

%prep
rm -rf $RPM_BUILD_ROOT

%install
tar xzf $RPM_SOURCE_DIR/%{name}-%{version}.tar.gz -C %{_tmppath}

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(-,root,root,-)
/usr/java/default/COPYRIGHT-jai.txt
/usr/java/default/DISTRIBUTIONREADME-jai.txt
/usr/java/default/LICENSE-jai.txt
/usr/java/default/THIRDPARTYLICENSEREADME-jai.txt
/usr/java/default/UNINSTALL-jai
/usr/java/default/jre/lib/i386/libmlib_jai.so
/usr/java/default/jre/lib/ext/jai_codec.jar
/usr/java/default/jre/lib/ext/jai_core.jar
/usr/java/default/jre/lib/ext/mlibwrapper_jai.jar

%changelog

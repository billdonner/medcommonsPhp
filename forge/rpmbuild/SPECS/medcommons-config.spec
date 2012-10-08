Name:		medcommons-config
Version:	0.2.44
Release:	1
Summary:	Configure Java for the MedCommons appliance

Group: 		MedCommons
License: 	MedCommons License

Source0:	%{name}-%{version}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}
BuildArch:	noarch

Requires:	wget yum yum-updatesd httpd iptables selinux-policy medcommons-gateway medcommons-mc_locals medcommons-tomcat

%description
Configures the various software components making up the MedCommons appliance.

%pre
sed -i -e 's|^SELINUX=.*|SELINUX=disabled|' /etc/selinux/config || :
sed -i -e 's|^#ServerName www.example.com:443|#ServerName www.example.com:443\n\nInclude "conf.d/rewrite.conf"\n|' /etc/httpd/conf.d/ssl.conf || :
#/usr/sbin/alternatives --install /usr/bin/java java /usr/java/jdk1.6.0_02/bin/java 2
#echo "2" | /usr/sbin/alternatives --config java

%prep
rm -rf $RPM_BUILD_ROOT

%install
tar xzf $RPM_SOURCE_DIR/%{name}-%{version}.tar.gz -C %{_tmppath}

%clean
rm -rf $RPM_BUILD_ROOT

%post
/sbin/chkconfig yum-updatesd off

%files
%defattr(-,root,root,-)
/etc/hosts
/etc/sysconfig/iptables
/etc/sysconfig/ip6tables
%attr(0755, root, root) /usr/bin/medcommons-config
%attr(0755, root, root) /usr/bin/medcommons-control
%attr(0755, root, root) /usr/bin/medcommons-update

%changelog
* Thu Aug 13 2007 DFW <donald@medcommons.net> 0.1
- Initial build.

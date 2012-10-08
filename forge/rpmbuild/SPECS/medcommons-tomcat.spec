Name:		medcommons-tomcat
Version:	6.0.14
Release:	3
Summary:	Apache Tomcat packaged for MedCommons

Group:		MedCommons
License:	Apache License

Source0:	%{name}-%{version}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}
BuildArch:	noarch

Requires:	jdk

%description
Apache Tomcat as packaged for the MedCommons appliance.

%prep
rm -rf $RPM_BUILD_ROOT

%install
tar xzf $RPM_SOURCE_DIR/%{name}-%{version}.tar.gz -C %{_tmppath}

%clean
rm -rf $RPM_BUILD_ROOT

%pre
/usr/sbin/useradd -M -c "Tomcat web server" -s /sbin/nologin tomcat || :

%post
rm /opt/apache-tomcat || :
ln -s /opt/apache-tomcat-6.0.14 /opt/apache-tomcat
chgrp -R tomcat /var/apache-tomcat
chmod -R 640 /var/apache-tomcat/conf
find /var/apache-tomcat/conf -type d -exec chmod 750 '{}' ';'
chown -R tomcat /var/apache-tomcat/work

%files
%defattr(-,root,root,-)
%attr(0755,root,root)/etc/init.d/tomcat
/opt/apache-tomcat-6.0.14
/var/apache-tomcat

%changelog

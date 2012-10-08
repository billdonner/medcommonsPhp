Name:		medcommons-identity
Version:	0.2.187
Release:	1
Summary:	MedCommons Identity

Group:		MedCommons
License:	MedCommons License

Source0:	%{name}-%{version}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}
BuildArch:	noarch

Requires:	jdk medcommons-tomcat

%description
Identity component of the MedCommons appliance.

%install
tar xzf $RPM_SOURCE_DIR/%{name}-%{version}.tar.gz -C %{_tmppath}

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(-,root,root,-)
/var/apache-tomcat/webapps/identity
/etc/httpd/conf.d/identity_ajp.conf

%changelog

Name:		medcommons-DDL
Version:	0.2.188
Release:	1
Summary:	MedCommons DDL

Group:		MedCommons
License:	MedCommons License

Source0:	%{name}-%{version}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}
BuildArch:	noarch

Requires:	jdk medcommons-jai medcommons-tomcat

%description
DDL components of the MedCommons appliance.

%install
tar xzf $RPM_SOURCE_DIR/%{name}-%{version}.tar.gz -C %{_tmppath}

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(-,root,root,-)
/opt/gateway/webapps/DDL
/etc/httpd/conf.d/ddl_ajp.conf

%changelog

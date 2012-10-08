Name:		medcommons-gateway
Version:	0.2.234
Release:	1
Summary:	MedCommons Gateway

Group:		MedCommons
License:	MedCommons License

Source0:	%{name}-%{version}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}
BuildArch:	noarch

Requires:	jdk medcommons-tomcat

%description
Gateway components of the MedCommons appliance.

%prep
rm -rf $RPM_BUILD_ROOT

%install
tar xzf $RPM_SOURCE_DIR/%{name}-%{version}.tar.gz -C %{_tmppath}

%clean
rm -rf $RPM_BUILD_ROOT

%pre
/usr/sbin/useradd -c "MedCommons Gateway" -M -d /opt/gateway -s /sbin/nologin gateway || :
rm -rf /opt/gateway/work/*
rm -rf /opt/gateway/webapps/router/*

%post
chown -RL gateway:gateway /opt/gateway/conf /opt/gateway/data /opt/gateway/logs /opt/gateway/temp /opt/gateway/work

%files
%defattr(-,root,root,-)
%attr(0755, root, root) /etc/init.d/gateway
/opt/gateway
/opt/apache-tomcat
/etc/httpd/conf.d/gateway_ajp.conf
/etc/httpd/conf.d/router_ajp.conf
%config(noreplace) /opt/gateway/conf/LocalBootParameters.properties

%changelog

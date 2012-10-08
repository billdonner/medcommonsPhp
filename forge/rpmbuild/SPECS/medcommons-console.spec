Name:		medcommons-console
Version:	0.2.201
Release:	1
Summary:	MedCommons Console

Group:		MedCommons
License:	MedCommons License

Source0:	%{name}-%{version}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}
BuildArch:	noarch

Requires:	Django httpd mod_python mod_ssl MySQL-python python-imaging medcommons-php medcommons-gateway medcommons-mc_backups

%description
Console components of the MedCommons appliance.

%prep
rm -rf $RPM_BUILD_ROOT

%install
tar xzf $RPM_SOURCE_DIR/%{name}-%{version}.tar.gz -C %{_tmppath}

%clean
rm -rf $RPM_BUILD_ROOT

%pre
/usr/sbin/useradd -c "MedCommons Admin" -M -d /var/www/console -s /sbin/nologin mc_admin || :

%post
#chown -R mc_admin:mc_admin /var/www/console /var/www/cgi-bin /var/www/html /var/www/mc_templates /var/www/php/local_settings.php /opt/gateway/conf/LocalBootParameters.properties
/var/www/console/bin/mc-permissions

%files
%defattr(-,root,root,-)
/var/www/console
/var/www/mc_templates
/var/www/cgi-bin/publish
/var/www/php/local_settings.php
/etc/httpd/conf.d/console.conf

%changelog

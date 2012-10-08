Name:		medcommons-php
Version:	0.2.192
Release:	1
Summary:	MedCommons PHP

Group:		MedCommons
License:	MedCommons License

Source0:	%{name}-%{version}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)
BuildArch:	noarch

Requires:	php php-mysql php-soap php-gd php-mbstring php-mcrypt php-xml php-pear-DB httpd mod_ssl

%description
PHP component of the MedCommons appliance.

%prep
%setup -q

%build

%install
install -DT rewrite.conf $RPM_BUILD_ROOT/etc/httpd/conf.d/rewrite.conf || :
install -DT medcommons.ini $RPM_BUILD_ROOT/etc/php.d/medcommons.ini || :
cd php
python deploy.py base $RPM_BUILD_ROOT/var/www

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(-,root,root,-)
/etc/httpd/conf.d/rewrite.conf
/etc/php.d/medcommons.ini
/var/www/php
/var/www/html

%changelog

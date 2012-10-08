Name:		medcommons-mc_backups
Version:	0.2.144
Release:	1
Summary:	MedCommons Backup

Group:		MedCommons
License:	MedCommons License

Source0:	%{name}-%{version}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}
BuildArch:	noarch

Requires:	SOAPpy python-crypto

%description
Console components of the MedCommons appliance.

%install
tar xzf $RPM_SOURCE_DIR/%{name}-%{version}.tar.gz -C %{_tmppath}

%clean
rm -rf $RPM_BUILD_ROOT

%pre
/usr/sbin/useradd -c "MedCommons Backup" -M -d /opt/mc_backups -s /sbin/nologin mc_backups || :

%post
chown -R mc_backups:mc_backups /opt/mc_backups

%files
%defattr(-,root,root,-)
%attr(0755, root, root) /etc/init.d/mc_backups
/opt/mc_backups


%changelog

Name:		medcommons-mc_locals
Version:	0.2.181
Release:	1
Summary:	MedCommons mc_locals

Group:		MedCommons
License:	MedCommons License

Source0:	%{name}-%{version}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}
BuildArch:	noarch

Requires:	SOAPpy

%description
Console components of the MedCommons appliance.

%install
tar xzf $RPM_SOURCE_DIR/%{name}-%{version}.tar.gz -C %{_tmppath}

%clean
rm -rf $RPM_BUILD_ROOT

%pre
/usr/sbin/useradd -c "MC local allocator" -M -d /usr/etc -s /sbin/nologin mc_locals || :

%files
%defattr(-,root,root,-)
%attr(0755, root, root) /etc/init.d/mc_locals
/usr/sbin/mc_locals.py
/usr/sbin/mc_locals.pyc
/usr/sbin/mc_locals.pyo
%config(noreplace) /usr/etc/mc_locals.rc

%changelog

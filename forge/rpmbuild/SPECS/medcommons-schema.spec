Name:		medcommons-schema
Version:	0.2.185
Release:	1
Summary:	MedCommons Schema

Group:		MedCommons
License:	MedCommons License

Source0:	%{name}-%{version}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}
BuildArch:	noarch

Requires:	mysql mysql-server medcommons-config medcommons-console

%description
Schema for the MedCommons appliance.

%install
tar xzf $RPM_SOURCE_DIR/%{name}-%{version}.tar.gz -C %{_tmppath}

%clean
rm -rf $RPM_BUILD_ROOT

%pre
if [ "$1" -gt 1 ]; then
   pushd /root/schema
   ls -1 ???_*.sql > /tmp/medcommons-schema.root.sql.pre
   popd
fi

%post
if [ "$1" -gt 1 ]; then
   pushd /root/schema
   sh forge-update.sh
   popd
fi

%files
%defattr(-,root,root,-)
/root/schema

%changelog

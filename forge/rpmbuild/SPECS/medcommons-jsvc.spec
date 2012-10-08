Name:		medcommons-jsvc
Version:	6.0.14
Release:	1
Summary:	jsvc for Apache Tomcat packaged for MedCommons

Group:		MedCommons
License:	Apache License

Source0:	%{name}-%{version}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-root
BuildArch:	i386

Requires:	jdk medcommons-tomcat
BuildRequires:	gcc

%description
jsvc for Apache Tomcat as packaged for the MedCommons appliance.

%prep
%setup -q

%build
/bin/sh configure --with-java=/usr/java/default
make

%install
rm -rf $RPM_BUILD_ROOT
mkdir -p $RPM_BUILD_ROOT/opt/apache-tomcat/bin
install -p -m 755 jsvc $RPM_BUILD_ROOT/opt/apache-tomcat/bin

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(-,root,root,-)
%attr(0755, root, root) /opt/apache-tomcat/bin/jsvc

%changelog
* Mon Oct 15 2007 Donald Way <donaldway@gmail.comn> - jsvc-6.0.14
- Initial build.

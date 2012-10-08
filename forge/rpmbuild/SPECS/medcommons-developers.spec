Name:		medcommons-developers
Version:	0.2.17
Release:	1
Summary:	Configure accounts for MedCommons developer staff

Group:		MedCommons
License:	MedCommons License

Source0:	%{name}-%{version}.tar.gz
BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)
BuildArch:	noarch

Requires:	openssh pam shadow-utils sudo

Requires(pre):	/usr/sbin/useradd
Requires(pre):	/bin/sed
Requires(pre):	/bin/grep
Requires(pre):	/usr/bin/rsync
Requires(pre):	/etc/pam.d/login
Requires(pre):	/etc/security/access.conf
Requires(pre):	/etc/sudoers
Requires(pre):	/etc/ssh/sshd_config

%description
Enables secure access to deployed appliances by MedCommons developers.

%prep
%setup

%build

%install
install -DT -m 600 adrian $RPM_BUILD_ROOT/home/adrian/.ssh/authorized_keys &>/dev/null || :
install -DT -m 600 bill $RPM_BUILD_ROOT/home/bill/.ssh/authorized_keys &>/dev/null || :
install -DT -m 600 boxer $RPM_BUILD_ROOT/home/boxer/.ssh/authorized_keys &>/dev/null || :
install -DT -m 600 caroline $RPM_BUILD_ROOT/home/caroline/.ssh/authorized_keys &>/dev/null || :
install -DT -m 600 donald $RPM_BUILD_ROOT/home/donald/.ssh/authorized_keys &>/dev/null || :
install -DT -m 600 sean $RPM_BUILD_ROOT/home/sean/.ssh/authorized_keys &>/dev/null || :
install -DT -m 600 simon $RPM_BUILD_ROOT/home/simon/.ssh/authorized_keys &>/dev/null || :
install -DT -m 600 terry $RPM_BUILD_ROOT/home/terry/.ssh/authorized_keys &>/dev/null || :

%clean
rm -rf $RPM_BUILD_ROOT

%pre
if [ "$1" = 1 ]; then
    mkdir -p /etc/medcommons/restore
    cp /etc/ssh/sshd_config /etc/pam.d/login /etc/security/access.conf /etc/sudoers /etc/medcommons/restore

    sed -i -e 's/^#\(PermitRootLogin[ \t]*\)yes/\1no/' /etc/ssh/sshd_config || :
    sed -i -e 's/^\(PasswordAuthentication[ \t]*\)yes/\1no/' /etc/ssh/sshd_config || :
    sed -i -e 's/^#\([ \t]*%wheel[ \t]*ALL=(ALL)[ \t]*ALL\)/\1/' /etc/sudoers || :
    if ! grep "^[ \t]*-[ \t]*:[ \t]*ALL[ \t]*EXCEPT[ \t]*root[ \t]*:[ \t]*LOCAL" /etc/security/access.conf &>/dev/null
    then
       echo "" >> /etc/security/access.conf || :
       echo "-:ALL EXCEPT root:LOCAL" >> /etc/security/access.conf || :
       echo "+:wheel:ALL" >> /etc/security/access.conf || :
       echo "-:ALL:-" >> /etc/security/access.conf || :
    fi
    sed -i -e 's/\(account[ \t]*required[ \t]*\)pam_nologin.so/\1pam_access.so/' /etc/pam.d/login || :

    mkdir /etc/skel/Maildir
    mkdir /etc/skel/Maildir/cur
    mkdir /etc/skel/Maildir/tmp
    mkdir /etc/skel/Maildir/new
    mkdir /etc/skel/.ssh

    chmod -R 0700 /etc/skel/Maildir
    chmod -R 0700 /etc/skel/.ssh
fi

/usr/sbin/useradd -c "Adrian Gropper" -m -G wheel adrian &>/dev/null || :
/usr/sbin/useradd -c "Bill Donner" -m -G wheel bill &>/dev/null || :
/usr/sbin/useradd -c "Caroline Cooper" -m -G wheel caroline &>/dev/null || :
/usr/sbin/useradd -c "Donald Way" -m -G wheel donald &>/dev/null || :
/usr/sbin/useradd -c "Nick Vasilatos" -m -G wheel boxer &>/dev/null || :
/usr/sbin/useradd -c "Sean Doyle" -m -G wheel sean &>/dev/null || :
/usr/sbin/useradd -c "Simon Sadedin" -m -G wheel simon &>/dev/null || :
/usr/sbin/useradd -c "Terence Way" -m -G wheel terry &>/dev/null || :

%files
%defattr(-,root,root,-)
%attr(0600, adrian, adrian) /home/adrian/.ssh/authorized_keys
%attr(0600, bill, bill) /home/bill/.ssh/authorized_keys
%attr(0600, boxer, boxer) /home/boxer/.ssh/authorized_keys
%attr(0600, caroline, caroline) /home/caroline/.ssh/authorized_keys
%attr(0600, donald, donald) /home/donald/.ssh/authorized_keys
%attr(0600, sean, sean) /home/sean/.ssh/authorized_keys
%attr(0600, simon, simon) /home/simon/.ssh/authorized_keys
%attr(0600, terry, terry) /home/terry/.ssh/authorized_keys

%changelog
* Sun Jul 22 2007 DFW <donald@medcommons.net> 0.1
- Initial build.

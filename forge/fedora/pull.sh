#SRC=rsync://mirror.anl.gov/fedora/linux
SRC=rsync://mirrors.kernel.org/fedora
rsync -vaH --delete-after --exclude=".*" --exclude=debug/ $SRC/updates/7/i386/ updates/7/i386


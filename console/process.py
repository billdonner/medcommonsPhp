#!/usr/bin/env python

from os import close, dup2, execlp, fork, open, \
	       O_RDONLY, O_WRONLY, O_CREAT, O_TRUNC

def spawn(cmd, stdin = None, stdout = None, stderr = None):
    """Spawn a subprocess, with extra-fine control over the command
    and the input/output of the subprocess.

    cmd must be an array of arguments, with the first element of the
    array as the command/executable to run.

    stdin, stdout, and stderr are either tuples (fd-in, fd-out) as
    returned by os.pipe(), or a string (a file to open), an integer
    file descriptor, or None to specify /dev/null

    Examples::
	>>> spawn(['ls'])

	>>> from os import pipe, fdopen
	>>> p = pipe()
	>>> spawn(['ls'], stdout = p)
	>>> fdr, fdw = p
	>>> close(fdw)
	>>> fdopen(fdr).read()
    """
    pid = fork()

    if pid == 0:
	# child process

	dup_in(stdin, 0)
	dup_out(stdout, 1)
	dup_out(stderr, 2)

	execlp(cmd[0], *cmd)

    return pid

def dup_in(spec, fd):
    if spec is None:
	spec = '/dev/null'

    if isinstance(spec, (tuple, list)):
	fdr, fdw = spec
	close(fdw)
    elif isinstance(spec, (unicode, str)):
	fdr = open(spec, O_RDONLY)
    else:
	fdr = spec

    if fdr != fd:
	dup2(fdr, fd)
	close(fdr)

def dup_out(spec, fd):
    if spec is None:
	spec = '/dev/null'

    if isinstance(spec, (tuple, list)):
	fdr, fdw = spec
	close(fdr)
    elif isinstance(spec, (unicode, str)):
	fdw = open(spec, O_WRONLY | O_CREAT | O_TRUNC)
    else:
	fdw = spec

    if fdw != fd:
	dup2(fdw, fd)
	close(fdw)

def _test():
    import doctest, utils
    return doctest.testmod(utils)

if __name__ == '__main__':
    _test()

#!/usr/bin/env python
# s3backups/pipes.py
# Copyright(c) 2007, Terence Way

__author__ = "Terence Way"
__email__  = "tway@medcommons.net"
__version__ = "1.0: August 12, 2007"

from os import popen as os_popen

__all__ = ['popen', 'spawn']

try:
    from os import close, dup2, execlp, fdopen, fork, open, pipe, \
	 waitpid, O_RDONLY

    _not_unix = False
except ImportError:

    pass

_not_unix = True

def popen(commands):
    r"""Executes an array of commands, connecting the standard output of
    one to the standard input of the other.

    The _commands_ parameter must be an array of commands, each command
    is an array of strings: the command line parameters.

    Returns a file object that is the output of the last command.

    Example::
	>>> f = popen([ ['ls'], ['grep', 'java'], ['grep', 'SSL'] ])
	>>> f.readlines()
	['OpenSSL.java\n']

    """
    if _not_unix:
	# inefficient, use shell to pipe everything
	return os_popen(build_command(commands))
    else:
	# efficient, but works only on Linux/MacOS, Unix derivatives
	r_out, w_out = pipe()

	status = spawn(commands, w_out)
	close(w_out)

	return fdopen(r_out, 'r')

def build_command(commands):
    """Build a chain of piped commands.  Escape out any messy characters.

    Examples::
	>>> build_command([ ['ls'], ['grep', 'java'], ['grep', 'SSL'] ])
	'ls | grep java | grep SSL'

	>>> build_command([ ['echo', 'a strange set of characters'], \
			    ['grep', '&|<>'] ])
	'echo "a strange set of characters" | grep "&|<>"'
    """
    return ' | '.join([' '.join([sh_escape(x) for x in a]) for a in commands])

def sh_escape(string):
    r"""Returns the shortest string that is escaped against shell characters.

    Examples::
	>>> sh_escape("this is a test")
	'"this is a test"'

	>>> sh_escape("foo|bar")
	'foo\\|bar'
    """
    s1 = '"' + escape_chars(string, '\\"') + '"'
    s2 = escape_chars(string, r"""\|&<> "'""")

    if len(s1) < len(s2):
	return s1
    else:
	return s2

def escape_chars(string, chars, escape='\\'):
    r"""Escape a set of characters.

    For each character in _chars_, if that character appears in
    _string_, then that character is prefixed by _escape_.

    Example::
	>>> escape_chars("this is a test", " t")
	'\\this\\ is\\ a\\ \\tes\\t'
    """
    for ch in chars:
	string = string.replace(ch, escape + ch)
    return string

def spawn(commands, fd_out = None, fd_err = None):
    """Executes an array of commands, connecting the standard output of
    one to the standard input of the other.  Does the same thing as
    shell pipes |, but without worrying about escaping shell characters.

    The _commands_ parameter must be an array of commands, each command
    is an array of strings: the command line parameters.  Example::
	[ ['ls'], ['grep', 'java'], ['grep', 'SSL'] ]
    This is the same as 'ls | grep java | grep SSL'

    The null device is used as the first process's standard input.

    _fd_out_ is used as the standard output of the last process.  If it
    is None, then the process's current standard output is used.

    _fd_err_ is used as the standard error for all processes.  If it
    is None, then the process's current standard error is used.

    Returns the exit status of the last command.
    """
    fd_in  = open('/dev/null', O_RDONLY)
    fd_out = fd_out or 1
    fd_err = fd_err or 2

    for command in commands[:-1]:
	r_pipe, w_pipe = pipe()
	_spawn1(command, fd_in, w_pipe, fd_err)
	close(fd_in)
	close(w_pipe)
	fd_in = r_pipe

    # last command
    pid = _spawn1(commands[-1], fd_in, fd_out, fd_err)
    close(fd_in)

    pid, status = waitpid(pid, 0)

    return status

def _spawn1(command, fd_in, fd_out, fd_err):
    """Spawns a separate process, with specified file descriptors
    as standard input, output, and error.

    Returns the PID of the new process.
    """
    pid = fork()

    if pid != 0:
	return pid

    # child process

    # standard input
    if fd_in != 0:
	dup2(fd_in, 0)
	close(fd_in)

    # standard output
    if fd_out != 1:
	dup2(fd_out, 1)
	close(fd_out)

    # standard error
    if fd_err != 2:
	dup2(fd_err, 2)
	close(fd_err)

    execlp(command[0], *command)
    # no return

def _test():
    import doctest, pipes
    return doctest.testmod(pipes)

if __name__ == '__main__':
    _test()


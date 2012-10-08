#!/usr/bin/env python

from glob import glob
from os import system, remove
from os.path import join, split
from sys import argv, exit

import re

def main(args):
	test_files = glob(join('..', '[0-9][0-9][0-9]*.sql'))
	test_files.sort()

	mysql = 'mysql ' + ' '.join(args[1:])
	system(mysql + ' <test_mysqldiff.sql')

	ntests = 0
	for test in test_files:

		print test
		name = split(test)[-1]

		# apply the update to test1
		system(mysql + ' mcxgood <' + test)

		# analyze the new database
		system('mysqldump --no-data --skip-comments --skip-add-drop-table mcxgood >mcx.sql')

		# apply mysqldiff-generated updates to test2
		system(mysql + " -e 'CREATE DATABASE `diff:mcx`'")
		system(mysql + ' diff:mcx <mcx.sql')
		x = system('../mysqldiff -n ../mcx_hints.py diff:mcx mcxtest -o test-' + name)
		if x != 0:
			print 'correct mysqldiff error', test
			exit(1)

		system(mysql + " -e 'DROP DATABASE `diff:mcx`'")
		system(mysql + ' mcxtest <test-' + name)

		if compare_mysqldump('mcxgood', 'mcxtest') != 0:
			print 'Whoops!', test
			exit(1)

		ntests +=1

	# Okay, we've proven that mysqldiff can handle stepwise refinement
	# now, how about we try from an empty database onwards
	# mcx.sql is the very latest db definition
	for i in range(len(test_files)):
		test = test_files[i]
		name = split(test)[-1]

		print test

		system(mysql + " -e 'DROP DATABASE mcxtest'")
		system(mysql + " -e 'CREATE DATABASE mcxtest'")

		for j in range(i):
			update = test_files[j]
			system(mysql + ' mcxtest <' + update)

		# we've brought mcxtest up to some point in the past
		# let's see if a massive update will work
		x = system('../mysqldiff -n ../mcx_hints.py mcxgood mcxtest -o test-' + name)
		if x != 0:
			print 'correct mysqldiff error test-' + name
			exit(1)

		system(mysql + ' mcxtest <test-' + name)

		if compare_mysqldump('mcxtest', 'mcxgood') != 0:
			print 'Whoops!', test
			exit(1)

		ntests += 1

	print
	print ntests, 'tests completed successfully'
	print 'Cleaning...'

	for test in test_files:
		name = split(test)[-1]
		remove('test-' + name)

	remove('key-mcxgood.sql')
	remove('key-mcxtest.sql')
	remove('sql-mcxgood.sql')
	remove('sql-mcxtest.sql')
	remove('mcxgood.sql')
	remove('mcxtest.sql')
	remove('mcx.sql')

KEY_RE = re.compile(r'^\s*((PRIMARY)|(UNIQUE))?\sKEY')

def compare_mysqldump(db1, db2):
	fn1 = db1 + '.sql'
	fn2 = db2 + '.sql'

	filter(db1, fn1)
	filter(db2, fn2)

	x = system('diff sql-%s sql-%s' % (fn1, fn2))

	if x != 0:
		return x

	x = system('diff key-%s key-%s' % (fn1, fn2))

	return x

def filter(db, fn):
	system('mysqldump --no-data --skip-comments ' + db + ' >' + fn)

	o1 = file('sql-' + fn, 'w')
	o2 = file('key-' + fn, 'w')
	a = []

	for line in file(fn):
		if KEY_RE.match(line):
			a.append(line)
		else:
			o1.write(line)

	a.sort()

	for line in a:
		line = line.rstrip()
		if line.endswith(','):
			line = line[:-1]

		print >>o2, line

if __name__ == '__main__':
	main(argv)

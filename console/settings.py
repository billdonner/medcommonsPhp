# Django settings for console project.

DEBUG = True
TEMPLATE_DEBUG = DEBUG

ADMINS = (
    ('Terence Way', 'tway@medcommons.net'),
)

ROOT_URL = '/'

INSTALL_DIR = './'
HOME_DIR = '~'

MANAGERS = ADMINS

DATABASE_ENGINE = 'mysql'   # 'postgresql', 'mysql', 'sqlite3' or 'ado_mssql'.
DATABASE_NAME = 'mcx'       # Or path to database file if using sqlite3.
DATABASE_USER = 'console_setup'
DATABASE_PASSWORD = ''
DATABASE_HOST = ''
DATABASE_PORT = ''

DATABASE_OPTIONS = {
	'init_command': 'SET storage_engine=InnoDB',
}

# Local time zone for this installation. All choices can be found here:
# http://www.postgresql.org/docs/current/static/datetime-keywords.html#DATETIME-TIMEZONE-SET-TABLE
TIME_ZONE = 'Pacific/Honolulu'

# Language code for this installation. All choices can be found here:
# http://www.w3.org/TR/REC-html40/struct/dirlang.html#langcodes
# http://blogs.law.harvard.edu/tech/stories/storyReader$15
LANGUAGE_CODE = 'en-us'

SITE_ID = 1

# Absolute path to the directory that holds media.
# Example: "/home/media/media.lawrence.com/"
MEDIA_ROOT = INSTALL_DIR + 'media/'

# URL that handles the media served from MEDIA_ROOT.
# Example: "http://media.lawrence.com"
MEDIA_URL = ROOT_URL + 'media/'

# URL prefix for admin media -- CSS, JavaScript and images. Make sure to use a
# trailing slash.
# Examples: "http://foo.com/media/", "/media/".
ADMIN_MEDIA_PREFIX = '/smedia/'

# Make this unique, and don't share it with anybody.
SECRET_KEY = '_hbiz!51i2!0womg0n0l#x^n(y&v67&_8t@_=de8h9kax1giyx'

# List of callables that know how to import templates from various sources.
TEMPLATE_LOADERS = (
    'django.template.loaders.filesystem.load_template_source',
    'django.template.loaders.app_directories.load_template_source',
#     'django.template.loaders.eggs.load_template_source',
)

MIDDLEWARE_CLASSES = (
    'django.middleware.common.CommonMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.middleware.doc.XViewMiddleware',
)

ROOT_URLCONF = 'urls'

TEMPLATE_DIRS = (
    # Put strings here, like "/home/html/django_templates".
    # Always use forward slashes, even on Windows.

    INSTALL_DIR + 'customize',
    INSTALL_DIR + 'templates',

)

INSTALLED_APPS = (
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.sites',
    'django.contrib.admin',

    'account',
    'admins',
    'users',
    'groups',
    'config',
    'idps',
    'logs',
    'security',
    'applications',
    'demos',
)


APPEND_SLASH = False

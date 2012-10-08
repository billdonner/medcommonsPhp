
dir='/var/www/nuke/html/php-ca/openssl/crypto'        # Where everything is kept
mkdir -p ${dir}/certs            # Where the issued certs are kept
mkdir -p ${dir}/crls             # Where the issued crl are kept
touch ${dir}/index.txt        # database index file.
mkdir -p ${dir}/certs            # default place for new certs.

mkdir -p ${dir}/cacerts      # The CA certificate
echo '01' > ${dir}/serial                   # The current serial number
mkdir -p ${dir}/crls/         # The current CRL
mkdir -p ${dir}/keys/           # The private key

chown -R apache:apache ${dir}


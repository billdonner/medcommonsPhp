"""\
MedCommons MTOM Implementation
Copyright 2006 MedCommons Inc.
@author Donald Way, MedCommons Inc.
"""

import logging, os, os.path, random, re, socket, sys, threading, time, xml.dom.minidom

head = '''\
POST %(path)s HTTP/1.1\r\n\
SOAPAction: ""\r\n\
Content-Type: multipart/related; type="application/xop+xml"; start="<soap.xml@xfire.codehaus.org>"; start-info="text/xml"; boundary="%(boundary)s"\r\n\
User-Agent: Mozilla/4.0 (compatible; Windows NT 5.0; Code written in Python +http://python.org)\r\n\
Host: %(host)s:%(port)d\r\n\
Expect: 100-continue\r\n\
Connection: close\r\n\
Transfer-Encoding: chunked\r\n\
\r\n\
'''

part = '''\r\n\
--%(boundary)s\r\n\
Content-ID: <%(cid)d@http://www.w3.org/2001/XMLSchema>\r\n\
Content-Type: application/octet-stream\r\n\
Content-Transfer-Encoding: binary\r\n\
\r\n\
'''

last = '''\r\n\
--%(boundary)s--\r\n\
'''

eval_init = 0
eval_term = 1
eval_ok = 2
eval_chunk = 3
eval_data = 4

mime_init = 0
mime_boundary = 1
mime_header = 2
mime_body = 3
mime_term = 4

class mtom:
    def __init__(self, params, status):
        self.params = params
        self.status = status
        self.debugin = None
        self.debugout = None
        self.eval_state = eval_init
        self.mime_state = mime_init
        self.result = None
        self.s = ''
        self.fd = None
        self.ssl = None
        for key, val in self.params.items():
            self.status.logger.debug('%s = %s', key, str(val))

    def name(self, s):
        # override
        return (s,)

    def walk(self, root):
        # override
        node = root.firstChild
        while node:
            if node.nodeType == xml.dom.Node.ELEMENT_NODE:
                self.walk(node)
            node = node.nextSibling

    def soap(self, doc):
        self.walk(doc.documentElement)

    def text(self, node):
        s = ''
        child = node.firstChild
        while child:
            if child.nodeType == xml.dom.Node.TEXT_NODE:
                s = s + child.nodeValue
            elif child.nodeType == xml.dom.Node.ELEMENT_NODE:
                s = s + self.text(child)
            child = child.nextSibling
        return s

    def recv(self):
        if self.ssl:
            try:
                s = self.ssl.read(0x1000)
            except socket.error:
                s = ''
            except:
                raise
        else:
            s = self.sock.recv(0x1000)
        if self.debugin:
            self.debugin.write(s)
        self.status.txrecv += len(s)
        return s

    def send(self, s):
        if self.ssl:
            self.ssl.write(s)
        else:
            self.sock.send(s)
        if self.debugout:
            self.debugout.write(s)
        self.status.txsend += len(s)

    def mime(self, s, crlf):
        if self.mime_state == mime_body:
            if s == '--%s--' % self.boundary:
                self.loop = False
                self.mime_state = mime_term
            elif s == '--%s' % self.boundary:
                self.mime_state = mime_header
            elif self.xop is not None:
                self.xop += s
                return
            elif self.fd:
                if self.crlf:
                    if crlf:
                        s = '\r\n' + s
                else:
                    self.crlf = True
                self.fd.write(s)
                self.status.tibytes += len(s)
                return
            if self.xop is not None:
                self.status.logger.info('SOAP message received.')
                self.soap(xml.dom.minidom.parseString(self.xop))
                self.xop = None
            elif self.fd:
                self.fd.close()
                self.fd = None
                self.status.tifiles += 1
                self.status.logger.info('File %d of %d saved.', self.status.tifiles, self.status.tnfiles)
        elif self.mime_state == mime_header:
            if s == '':
                self.mime_state = mime_body
                if self.headers['content-type'].find('application/xop+xml') != -1:
                    self.xop = ''
                    self.status.logger.debug('begin receiving SOAP message')
                elif self.headers['content-transfer-encoding'].find('binary') != -1 and 'content-id' in self.headers:
                    ft = self.name(self.headers['content-id'][1:-1])
                    fp = '/'.join(ft[:-1])
                    if fp and not os.path.exists(fp):
                        os.makedirs(fp, 0x1ff)
                    self.status.logger.debug("begin receiving file '%s'", ft[-1])
                    fn = '/'.join(ft)
                    self.fd = open(fn, 'wb')
                    os.chmod(fn, 0x1ff)
                    self.crlf = False
            elif s[0] == ' ' or s[0] == '\t':
                if self.header:
                    self.headers[self.header] += s
            else:
                i = s.find(':')
                if i != -1:
                    self.header = s[:i].lower()
                    self.headers[self.header] = s[i + 1:].strip()
        elif self.mime_state == mime_init:
            self.mime_state = mime_boundary
        elif self.mime_state == mime_boundary:
            self.headers = {}
            self.header = None
            self.xop = None
            self.mime_state = mime_header
        elif self.mime_state == mime_term:
            pass

    def eval(self, s):
        if self.eval_state == eval_init:
            self.header = ''
            self.headers = {}
            if s.startswith('HTTP/1.'):
                self.status.logger.debug('server HTTP reply: %s', s[8:].strip())
                t = s[8:].strip()[:3]
                if t == '100':
                    self.eval_state = eval_term
                elif t == '200':
                    self.eval_state = eval_ok
        elif self.eval_state == eval_term:
            self.eval_state = eval_ok
        elif self.eval_state == eval_ok:
            if s == '':
                self.boundary = ''
                if 'content-type' in self.headers:
                    mo = re.search('boundary="([^"]*)"', self.headers['content-type'])
                    if mo:
                        self.boundary = mo.group(1)
                if 'transfer-encoding' in self.headers and self.headers['transfer-encoding'] == 'chunked':
                    self.eval_state = eval_chunk
                    self.status.logger.debug('transfer-encoding: chunked')
                else:
                    self.eval_state = eval_data
            elif s[0] == ' ' or s[0] == '\t':
                if self.header:
                    self.headers[self.header] += s
            else:
                i = s.find(':')
                if i != -1:
                    self.header = s[:i].lower()
                    self.headers[self.header] = s[i + 1:].strip()
        elif self.eval_state == eval_chunk:
            n = int(s, 16)
            while len(self.s) < n + 2:
                self.s += self.recv()
            crlf = False
            while n >= 0:
                i = self.s.find('\r\n')
                s = self.s[:i]
                self.s = self.s[i + 2:]
                n -= i + 2
                self.mime(s, crlf)
                crlf = True
        elif self.eval_state == eval_data:
            self.mime(s, True)

    def mtom(self, xop, files):
        self.sock = socket.socket()
        try:
            if self.params['i']:
                fn = 'debug.in'
                self.debugin = open(fn, 'ab')
                os.chmod(fn, 0x1ff)
                self.status.logger.debug('debugin fd created')
            if self.params['o']:
                fn = 'debug.out'
                self.debugout = open(fn, 'ab')
                os.chmod(fn, 0x1ff)
                self.status.logger.debug('debugout fd created')
            #self.sock.settimeout(30.)
            self.sock.connect((self.params['host'], self.params['port']))
            self.status.logger.info('Connected to %s:%d.', self.params['host'], self.params['port'])
            if self.params['protocol'] == 'https':
                self.ssl = socket.ssl(self.sock)
                self.status.logger.info('Connection is secure.')
            else:
                self.status.logger.warning('Connection is not secure!')
            try:
                self.send(head % self.params)
                s = ''
                while True:
                    s += self.recv()
                    i = s.find('\r\n\r\n')
                    if i != -1:
                        break
                s = xop
                for file in files:
                    params = self.params.copy()
                    params.update(file)
                    fd = open(file['path'], 'rb')
                    try:
                        s += part % params
                        while True:
                            if self.status.cancelled:
                                raise cancel()
                            ss = fd.read(0x1000)
                            if ss:
                                self.status.tibytes += len(ss)
                                s += ss
                                if len(s) >= 0x2000:
                                    self.send('2000\r\n%s\r\n' % s[:0x2000])
                                    s = s[0x2000:]
                            else:
                                break
                        self.status.tifiles += 1
                    finally:
                        fd.close()
                s += last % self.params
                self.send('%x\r\n' % len(s) + s + '\r\n0\r\n\r\n')
                self.status.logger.info('Request sent.')
            finally:
                self.loop = True
                while self.loop:
                    if self.status.cancelled:
                        raise cancel()
                    i = self.s.find('\r\n')
                    if i == -1:
                        self.s += self.recv()
                    else:
                        s = self.s[:i]
                        self.s = self.s[i + 2:]
                        self.eval(s)
        finally:
            self.sock.close()
            if self.fd:
                self.fd.close()
            if self.debugin:
                self.debugin.close()
            if self.debugout:
                self.debugout.close()
            self.status.lock.acquire()
            self.status.tibytes = self.status.tnbytes = None
            self.status.tifiles = self.status.tnfiles = None
            self.status.lock.release()
KB = 1024
MB = 1024 * KB
GB = 1024 * MB
TB = 1024 * GB

class status(threading.Thread):
    def __init__(self, name):
        threading.Thread.__init__(self)
        self.setDaemon(True)
        self.logger = logging.getLogger(name)
        self.cancelled = None
        self.error = None
        self.complete = None
        self.lock = threading.Lock()
        self.tifiles = self.tnfiles = None
        self.tibytes = self.tnbytes = None
        self.txrecv = self.tyrecv = 0
        self.tarecv = [0] * 10
        self.txsend = self.tysend = 0
        self.tasend = [0] * 10
        self.start()

    def update(self, status):
        self.lock.acquire()
        try:
            for key, val in status.items():
                setattr(self, key, val)
        finally:
            self.lock.release()

    def measure(self, files):
        tnbytes = 0
        for file in files:
            tnbytes += os.stat(file['path']).st_size
        self.lock.acquire()
        self.tibytes = 0
        self.tnbytes = tnbytes
        self.tifiles = 0
        self.tnfiles = len(files)
        self.lock.release()

    def formatbps(self, bps):
        if bps < 10 * KB:
            return '%4.2fK/s' % (bps / KB)
        elif bps < 100 * KB:
            return '%4.1fK/s' % (bps / KB)
        elif bps < 10 * MB:
            return '%4.2fM/s' % (bps / MB)
        elif bps < 100 * MB:
            return '%4.1fM/s' % (bps / MB)
        elif bps < 10 * GB:
            return '%4.2fG/s' % (bps / GB)
        elif bps < 100 * GB:
            return '%4.1fG/s' % (bps / GB)
        elif bps < 10 * TB:
            return '%4.2fT/s' % (bps / TB)
        elif bps < 100 * TB:
            return '%4.1fT/s' % (bps / TB)
        else:
            return '  ! 0  '

    def run(self):
        while self.error is None and self.complete is None:
            time.sleep(.1)
            txrecv = self.txrecv
            txsend = self.txsend
            self.lock.acquire()
            self.tarecv = self.tarecv[1:]
            self.tarecv.append(txrecv - self.tyrecv)
            self.tyrecv = txrecv
            self.tasend = self.tasend[1:]
            self.tasend.append(txsend - self.tysend)
            self.tysend = txsend
            self.lock.release()
            if self.error or self.complete:
                break

    def __getitem__(self, key):
        if hasattr(self, key):
            return getattr(self, key)
        else:
            return None

class thread(threading.Thread):
    def __init__(self, status):
        threading.Thread.__init__(self)
        self.setDaemon(True)
        self.status = status
        self.tisecs = time.time()
        self.loop = True

    def run(self):
        bnchars = 55
        while self.loop:
            tibytes = self.status.tibytes
            tnbytes = self.status.tnbytes
            tifiles = self.status.tifiles
            tnfiles = self.status.tnfiles
            if tibytes is not None and tnbytes is not None and tifiles is not None and tnfiles is not None:
                tpercent = float(tibytes) / tnbytes
                bichars = int(tpercent * bnchars)
                sys.stdout.write('\r[%s] %s %d of %d' % \
                    ('#' * bichars + ' ' * (bnchars - bichars),
                     self.formatbps(float(tibytes) / (time.time() - self.tisecs)),
                     tifiles, tnfiles))
                sys.stdout.flush()
            time.sleep(1.0)

    def stop(self):
        if self.loop:
            self.loop = False
            self.join()

class cancel(Exception):
    def __init__(self):
        pass
    def __str__(self):
        return 'the user did'

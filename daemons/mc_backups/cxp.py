"""\
MedCommons CXP Python Implementation
Copyright 2006 MedCommons Inc.
@author Donald Way, MedCommons Inc.
"""

import logging, random, xml.dom.minidom
import mtom

params = { 
    'x': False,
    'i': False,
    'o': False,
    'protocol': 'http',
    'host': 'localhost',
    'port': 9080,
    'path': '/gateway/services/CXP2',
    'boundary': '----=_Part_0_%d.%d' % (random.randint(0, 0xffffffffl),
                                        random.randint(0, 0xffffffffl))
}

class delete(mtom.mtom):
    xop1 = '''\r\n\
--%(boundary)s\r\n\
Content-Type: application/xop+xml; charset=UTF-8; type="text/xml"\r\n\
Content-Transfer-Encoding: 8bit\r\n\
Content-ID: <soap.xml@xfire.codehaus.org>\r\n\
\r\n\
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <soap:Body>
    <delete xmlns="http://org.cxp2">
      <in0 xmlns="http://org.cxp2">
        <docinfo xmlns="http://cxp2.org">
'''

    xop2 = '''\
          <Document> 
            <contentType xsi:nil="true" />
            <data xsi:nil="true" />
            <description xsi:nil="true" />
            <documentName xsi:nil="true" />
            <guid>%(guid)s</guid>
            <parentName xsi:nil="true" />
            <sha1 xsi:nil="true" />
          </Document>
'''

    xop3 = '''\
        </docinfo>
        <registryParameters xmlns="http://cxp2.org" />
        <storageId xmlns="http://cxp2.org">%(storageId)s</storageId>
      </in0>
    </delete>
  </soap:Body>
</soap:Envelope>
'''

    def soap(self, doc):
        mtom.mtom.soap(self, doc)
        if hasattr(self.status, 'serverStatus') and hasattr(self.status, 'serverReason'):
            if self.status.serverStatus == '200':
                self.status.logger.debug('server SOAP reply: %s %s',
                                         self.status.serverStatus, self.status.serverReason)
            else:
                raise servererror(int(self.status.serverStatus), self.status.serverReason)
        else:
            raise serversilent()

    def walk(self, root):
        node = root.firstChild
        while node:
            if node.nodeType == xml.dom.Node.ELEMENT_NODE:
                if node.nodeName == 'value':
                    self.status.lock.acquire()
                    self.status.tibytes = 0
                    self.status.tnbytes = int(self.text(node))
                    self.status.lock.release()
                elif node.nodeName == 'status':
                    self.status.serverStatus = self.text(node)
                elif node.nodeName == 'reason':
                    self.status.serverReason = self.text(node)
                else:
                    self.walk(node)
            node = node.nextSibling

    def cxp(self, study):
        xop = delete.xop1 % self.params
        for series in study['elements']:
            params = self.params.copy()
            params.update(series)
            xop += delete.xop2 % params
        xop += delete.xop3 % self.params
        self.status.logger.debug('sending DELETE request')
        self.mtom(xop, [])

class head(mtom.mtom):
    xop1 = '''\r\n\
--%(boundary)s\r\n\
Content-Type: application/xop+xml; charset=UTF-8; type="text/xml"\r\n\
Content-Transfer-Encoding: 8bit\r\n\
Content-ID: <soap.xml@xfire.codehaus.org>\r\n\
\r\n\
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <soap:Body>
    <get xmlns="http://org.cxp2">
      <in0 xmlns="http://org.cxp2">
        <docinfo xmlns="http://cxp2.org">
'''

    xop2 = '''\
          <Document> 
            <contentType xsi:nil="true" />
            <data xsi:nil="true" />
            <description xsi:nil="true" />
            <documentName xsi:nil="true" />
            <guid>%(guid)s</guid>
            <parentName xsi:nil="true" />
            <sha1 xsi:nil="true" />
          </Document>
'''

    xop3 = '''\
        </docinfo>
        <registryParameters xmlns="http://cxp2.org">
          <RegistryParameters>
            <parameters>
              <Parameter>
                <name>RetrieveDocument</name>
                <value>FALSE</value>
              </Parameter>
            </parameters>
            <registryId>medcommons.net</registryId>
            <registryName>MedCommons, Inc</registryName>
          </RegistryParameters>
        </registryParameters>
        <storageId xmlns="http://cxp2.org">%(storageId)s</storageId>
      </in0>
    </get>
  </soap:Body>
</soap:Envelope>
'''

    def soap(self, doc):
        mtom.mtom.soap(self, doc)
        if hasattr(self.status, 'serverStatus') and hasattr(self.status, 'serverReason'):
            if self.status.serverStatus == '200':
                self.status.logger.debug('server SOAP reply: %s %s',
                                         self.status.serverStatus, self.status.serverReason)
            else:
                raise servererror(int(self.status.serverStatus), self.status.serverReason)
        else:
            raise serversilent()

    def walk(self, root):
        node = root.firstChild
        while node:
            if node.nodeType == xml.dom.Node.ELEMENT_NODE:
                if node.nodeName == 'value':
                    self.status.lock.acquire()
                    self.status.tibytes = 0
                    self.status.tnbytes = int(self.text(node))
                    self.status.lock.release()
                elif node.nodeName == 'status':
                    self.status.serverStatus = self.text(node)
                elif node.nodeName == 'reason':
                    self.status.serverReason = self.text(node)
                else:
                    self.walk(node)
            node = node.nextSibling

    def cxp(self, study):
        xop = head.xop1 % self.params
        for series in study['elements']:
            params = self.params.copy()
            params.update(series)
            xop += head.xop2 % params
        xop += head.xop3 % self.params
        self.status.logger.debug('sending HEAD request')
        self.mtom(xop, [])

class get(mtom.mtom):
    xop1 = '''\r\n\
--%(boundary)s\r\n\
Content-Type: application/xop+xml; charset=UTF-8; type="text/xml"\r\n\
Content-Transfer-Encoding: 8bit\r\n\
Content-ID: <soap.xml@xfire.codehaus.org>\r\n\
\r\n\
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <soap:Body>
    <get xmlns="http://org.cxp2">
      <in0 xmlns="http://org.cxp2">
        <docinfo xmlns="http://cxp2.org">
'''

    xop2 = '''\
          <Document> 
            <contentType xsi:nil="true" />
            <data xsi:nil="true" />
            <description xsi:nil="true" />
            <documentName xsi:nil="true" />
            <guid>%(guid)s</guid>
            <parentName xsi:nil="true" />
            <sha1 xsi:nil="true" />
          </Document>
'''

    xop3 = '''\
        </docinfo>
        <registryParameters xmlns="http://cxp2.org" />
        <storageId xmlns="http://cxp2.org">%(storageId)s</storageId>
      </in0>
    </get>
  </soap:Body>
</soap:Envelope>
'''

    def name(self, s):
        if 'guid' in self.names[s] and 'sha1' in self.names[s]:
            guid = self.names[s]['guid']
            sha1 = self.names[s]['sha1']
            if guid == sha1:
                return (guid,)
            else:
                return (guid, sha1)

    def walkDocument(self, root):
        node = root.firstChild
        while node:
            if node.nodeType == xml.dom.Node.ELEMENT_NODE:
                if node.nodeName == 'Include':
                    self.names[node.getAttribute('href')[4:]] = self.walkDoc
                elif node.nodeName in ('contentType', 'guid', 'sha1'):
                    self.walkDoc[node.nodeName] = self.text(node)
                self.walkDocument(node)
            node = node.nextSibling

    def walk(self, root):
        node = root.firstChild
        while node:
            if node.nodeType == xml.dom.Node.ELEMENT_NODE:
                if node.nodeName == 'Document':
                    self.walkDoc = {}
                    self.walkDocument(node)
                elif node.nodeName == 'value':
                    self.status.tibytes = 0
                    self.status.tnbytes = int(self.text(node))
                elif node.nodeName == 'status':
                    self.status.serverStatus = self.text(node)
                elif node.nodeName == 'reason':
                    self.status.serverReason = self.text(node)
                else:
                    self.walk(node)
            node = node.nextSibling

    def soap(self, doc):
        self.status.lock.acquire()
        mtom.mtom.soap(self, doc)
        self.status.tifiles = 0
        self.status.tnfiles = len(self.names)
        self.status.lock.release()
        if hasattr(self.status, 'serverStatus') and hasattr(self.status, 'serverReason'):
            if self.status.serverStatus == '200':
                self.status.logger.debug('server SOAP reply: %s %s',
                                         self.status.serverStatus, self.status.serverReason)
            else:
                raise servererror(int(self.status.serverStatus), self.status.serverReason)
        else:
            raise serversilent()
        if self.status.tnfiles:
            tnfiles = self.status.tnfiles
        else:
            tnfiles = 0
        if self.status.tnbytes:
            tnbytes = self.status.tnbytes
        else:
            tnbytes = 0
        self.status.logger.info('Receiving %d files consisting of %d bytes total.', tnfiles, tnbytes)

    def cxp(self, study):
        xop = get.xop1 % self.params
        for series in study['elements']:
            params = self.params.copy()
            params.update(series)
            xop += get.xop2 % params
        xop += get.xop3 % self.params
        self.names = {}
        self.status.logger.debug('sending GET request')
        self.mtom(xop, [])

class put(mtom.mtom):
    xop1 = '''\r\n\
--%(boundary)s\r\n\
Content-Type: application/xop+xml; charset=UTF-8; type="text/xml"\r\n\
Content-Transfer-Encoding: 8bit\r\n\
Content-ID: <soap.xml@xfire.codehaus.org>\r\n\
\r\n\
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <soap:Body>
    <put xmlns="http://org.cxp2">
      <in0 xmlns="http://org.cxp2">
        <docinfo xmlns="http://cxp2.org">
'''

    xop2 = '''\
          <Document> 
            <contentType>%(contentType)s</contentType>
            <data xmlns:ns1="http://www.w3.org/2004/11/xmlmime" ns1:mimeType="application/octet-stream">
              <Include xmlns="http://www.w3.org/2004/08/xop/include" href="cid:%(cid)d@http://www.w3.org/2001/XMLSchema" />
            </data>
            %(descriptionElement)s
            %(documentNameElement)s
            %(guidElement)s
            %(parentNameElement)s
            %(sha1Element)s
          </Document>
'''

    xop3a = '''\
        </docinfo>
        <registryParameters xmlns="http://cxp2.org" />
        <storageId xmlns="http://cxp2.org">%(storageId)s</storageId>
      </in0>
    </put>
  </soap:Body>
</soap:Envelope>
'''

    xop3b = '''\
        </docinfo>
        <registryParameters xmlns="http://cxp2.org">
          <RegistryParameters>
            <parameters>
              <Parameter>
                <name>sponsorGroupAccountId</name>
                <value>%(groupAccountId)s</value>
              </Parameter>
            </parameters>
            <registryId>medcommons.net</registryId>
            <registryName>MedCommons, Inc</registryName>
          </RegistryParameters>
        </registryParameters>
        <storageId xmlns="http://cxp2.org">%(storageId)s</storageId>
      </in0>
    </put>
  </soap:Body>
</soap:Envelope>
'''

    def soap(self, doc):
        self.registryParameters = self.parameter = False
        self.registryParameterName = ''
        mtom.mtom.soap(self, doc)
        if hasattr(self.status, 'serverStatus') and hasattr(self.status, 'serverReason'):
            if self.status.serverStatus == '200':
                self.status.logger.debug('server SOAP reply: %s %s',
                                         self.status.serverStatus, self.status.serverReason)
            else:
                raise servererror(int(self.status.serverStatus), self.status.serverReason)
        else:
            raise serversilent()

    def walk(self, root):
        node = root.firstChild
        while node:
            if node.nodeType == xml.dom.Node.ELEMENT_NODE:
                if node.nodeName == 'registryParameters':
                    self.registryParameters = True
                    self.walk(node)
                    self.registryParameters = False
                elif node.nodeName == 'Parameter' and self.registryParameters:
                    self.parameter = True
                    self.walk(node)
                    self.parameter = False
                elif node.nodeName == 'name' and self.parameter:
                    self.registryParameterName = self.text(node)
                elif node.nodeName == 'value' and self.parameter:
                    if self.registryParameterName == 'StorageId':
                        self.params['storageId'] = self.text(node)
                elif node.nodeName == 'status':
                    self.status.serverStatus = self.text(node)
                elif node.nodeName == 'reason':
                    self.status.serverReason = self.text(node)
                elif node.nodeName == 'guid':
                    self.params['documentGuid'] = self.text(node)
                else:
                    self.walk(node)
            node = node.nextSibling

    def cxp(self, study):
        if 'groupAccountId' in self.params:
            xop3 = put.xop3b
        else:
            xop3 = put.xop3a
        def cxp(params, item):
            item['cid'] = random.randint(0, 0xffffffffffffl)
            params.update(item)
            for elem in ['description', 'documentName', 'guid', 'parentName', 'sha1']:
                if elem in params:
                    params['%sElement' % elem] = '<%s>%s</%s>' % (elem, params[elem], elem)
                else:
                    params['%sElement' % elem] = '<%s xsi:nil="true" />' % elem
            return put.xop2 % params
        if True:
            pass
        elif self.params['x']:
            delete(self.params, self.status).cxp(study)
        else:
            try:
                head(self.params, self.status).cxp(study)
            except servererror, e:
                self.status.logger.warning(str(e))
                self.status.logger.warning('Attempt to see if file/study already on server fails.')
        files = []
        xop = put.xop1 % self.params
        for e in study['elements']:
            if 'elements' in e:
                files.extend(e['elements'])
                for ee in e['elements']:
                    xop += cxp(self.params.copy(), ee)
            else:
                files.append(e)
                xop += cxp(self.params.copy(), e)
        xop += xop3 % self.params
        self.status.measure(files)
        self.status.logger.debug('sending PUT request containing %d files', len(files))
        self.mtom(xop, files)

class status(mtom.status):
    def __init__(self, name):
        mtom.status.__init__(self, name)
        self.tiseries = self.tnseries = None
        self.detail = None

class servererror(Exception):
    def __init__(self, status, reason):
        self.status = status
        self.reason = reason
    def __str__(self):
        return '%d %s' % (self.status, self.reason)

class serversilent(Exception):
    def __init__(self):
        pass
    def __str__(self):
        return 'No response from server.'

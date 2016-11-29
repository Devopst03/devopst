# Simple interface to the Sonatype Nexus REST API

import urllib2, sys, re, binascii
from xml.etree.ElementTree import ElementTree

HOST = 'http://maven.mode.com/nexus'
API = '%s/service/local' % HOST
USER = 'admin'
PASSWORD = 'admin123'

def delete_url(url, headers=None):
    opener = urllib2.build_opener(urllib2.HTTPHandler)
    req = urllib2.Request(url)
    req.get_method = lambda: 'DELETE'
    if headers is not None:
        for k, v in headers.items():
            req.add_header(k, v)
    return opener.open(req).read()

class Repo:

    def __init__(self, name):
        self.name = name

    def get(self, path):
        return Folder(self, path)

class Entity:

    def __init__(self, repo, path):
        self.repo = repo
        self.path = path
        self.filename = re.search("/([^/]+)/?$", self.path).group(1)
        self._xml = None

    def __repr__(self):
        return "<%s: %s %s>" % (self.__class__, self.repo.name, self.path)

    def url(self):
        return "%s/repositories/%s/content%s" % (API, self.repo.name, self.path)

    def xml(self):
        if self._xml is None:
            self._xml = ElementTree(file=urllib2.urlopen(self.url()))
        return self._xml

    def delete(self):
        print "DELETE %s" % self.url()
        print delete_url(self.url(), headers={'Authorization': 'Basic %s' % binascii.b2a_base64('%s:%s' % (USER, PASSWORD)).strip()})

class File(Entity):
    pass

class Folder(Entity):
    def __iter__(self):
        for child in self.xml().findall("data/content-item"):
            path = child.findtext("relativePath")
            if child.findtext("leaf") == 'true':
                yield File(self.repo, path)
            else:
                yield Folder(self.repo, path)

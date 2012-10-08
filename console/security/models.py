from django.db import models

class Certificate(models.Model):

   issued = models.DateTimeField()

   CN = models.CharField(maxlength=64)
   C  = models.CharField(maxlength=2)
   ST = models.CharField(maxlength=64)
   L  = models.CharField(maxlength=64)
   O  = models.CharField(maxlength=64)
   OU = models.CharField(maxlength=64, blank=True)

   key = models.TextField(maxlength=2048)
   csr = models.TextField(maxlength=2048)
   crt = models.TextField(maxlength=2048, null=True)

from django.db import models

class Source(models.Model):
    class Meta:
        db_table = 'log_sources'

    name = models.CharField(maxlength = 16, db_index = True)
    path = models.CharField(maxlength = 256)

    def __str__(self):
        return self.name
    
    class Admin: pass

class Entry(models.Model):
    class Meta:
        verbose_name = 'entry'
        verbose_name_plural = 'entries'
        db_table = 'log_entries'

    SEVERITIES = [
        ('D', 'Debug'),
        ('I', 'Info'),
        ('W', 'Warning'),
        ('E', 'Error'),
        ('S', 'Severe')
        ]

    datetime = models.DateTimeField(db_index = True)
    source = models.ForeignKey(Source)

    severity = models.CharField(maxlength = 1, choices = SEVERITIES)

    message = models.CharField(maxlength = 256)

    class Admin: pass

from django.db import models

class MCProperty(models.Model):
    property = models.CharField(maxlength=765,
                                primary_key = True)
    value = models.CharField(maxlength=765)
    infourl = models.CharField(maxlength=765)
    comment = models.CharField(maxlength=765)

    class Meta:
        db_table = 'mcproperties'

def get_property(name):
    try:
        p = MCProperty.objects.get(property='ac' + name)
        return parse_property(p.value)
    except MCProperty.DoesNotExist:
        return None

def parse_property(value):
    try:
	return int(value)
    except ValueError:
	return value

from django.db import models

class Numbers(models.Model):
    name = models.CharField(maxlength = 32,
                            db_index = True)

    base = models.FloatField(max_digits = 16, decimal_places = 0)
    leap = models.FloatField(max_digits = 16, decimal_places = 0)
    iterations = models.IntegerField()
    seed = models.FloatField(max_digits = 16, decimal_places = 0)

    class Meta:
        db_table = 'alloc_numbers'

class Appliance(models.Model):
    name = models.CharField(maxlength = 32)
    url = models.URLField()
    email = models.EmailField()
    appliance_type = models.CharField(maxlength=8, blank=True)

    def base(self):
        return self.url or 'https://' + self.name

    def httpbase(self):
        return 'http://' + self.name

    class Meta:
        db_table = 'appliances'

class Log(models.Model):
    numbers = models.ForeignKey(Numbers)
    seed = models.FloatField(max_digits = 16, decimal_places = 0)

    datetime = models.DateTimeField()
    appliance = models.ForeignKey(Appliance)

    ipaddr = models.IPAddressField()

    class Meta:
        db_table = 'alloc_log'

������������� ��� denver

������������ ���������� � configuration.php
index.php �������� ������ ���� ������������� �������
�������� ������
rebuild_database - ����������� ��� ������������� ���������� - ��������� ������� � ���� ������
task - ������������ ���������� csv �����

cron.php ������ ���� ���������� ��� ������� ������ ��-����������. �� ��������� ������ �������� ������� � �������� �������� � ������������ � �������� ������.
cron.php ����� ����� ������� � �������� - ��� �������� ����� �����������, �������� ������ �������� �����
�������������: cd <������� �������> && php -f cron.php

�� ����� ����� 5000 ������.
� ���� ������� 100 ������ ������� ����� 2 ����� (��� ����� �������� ��������)


��������� ����������

PHP 5.3 � ���������� cURL.


���������� �� ���������

1. ������� � ������� InSales
	2.1. ��������� �� ������� ����������/�������������
	2.2. �������� ���� �������
��������
Access Key-1  Del_ico
�������������:	d6db90ca9a04291079db86be4d85d6ba
������:	c4af6e1ea8613a04d19089014ecfaf07
������ URL:	http://apikey:password@hostname/admin/resource.xml
������ URL:	http://d6db90ca9a04291079db86be4d85d6ba:c4af6e1ea8613a04d19089014ecfaf07@aaabbbccc.myinsales.ru/admin/orders.xml
���� �����������:	14.07.2014

2. ���������� ��� ����� �� ������ �� ��������
3. �������� ���� ������ MySQL
4. �������������� ���� configuration.php
	public $sitename = '������������� ����������'; // �������� ������������ �����-������
	public $host = 'mysql.hostinger.ru'; // ������ ���� ������
	public $user = 'u266351659_tri'; // ����� ���� ������
	public $password = 'dSmOfOyH1b'; // ������ ���� ������
	public $db = 'u266351659_tri';  // �������� ���� ������
	public $dbprefix = 'tursportopt_';  // ������� ������ � ���� ������
	public $my_insales_domain = 'aaabbbccc.myinsales.ru';  // ����� � InSales
	// Access Key ��� �����
	public $insales_api_key = 'd6db90ca9a04291079db86be4d85d6ba';  // Access Key � InSales
	public $insales_password = 'c4af6e1ea8613a04d19089014ecfaf07';  // Access Key � InSales
	public $imagehost = 'http://aaabbbccc.esy.es/'; // ���� ��� ���������� ����������� ����������� (����������� � �������� �������� � ���� ��������
	public $imagedir = 'images/'; // ���������� ��� ���������� ����������� �����������
5. �������� �������� index.php �� ��������
	5.1. �������� ����� rebuild_database � ������� ������ Go!
6. �������� ������ ���������� ��������� � ��������� � ����� �������������/Cron-������ 
	6.1. �������� * * * * *	/usr/bin/php /home/u266351659/cron.php - ���������� cron.php ������ ������
	6.1. �������� 0 0,12 * * *	/usr/bin/php /home/u266351659/task2.php - ���������� task2.php ��� ���� � ����
7. ��������� ��������� ���������


cd /var/www/vrulin/data/www/tritumana.ru/tritumana-master && /usr/bin/php install.php >/dev/null 2>&1
cd /var/www/vrulin/data/www/tritumana.ru/tritumana-master && /usr/bin/php task1.php >/dev/null 2>&1
cd /var/www/vrulin/data/www/tritumana.ru/tritumana-master && /usr/bin/php task2.php >/dev/null 2>&1
cd /var/www/vrulin/data/www/tritumana.ru/tritumana-master && /usr/bin/php cron.php >/dev/null 2>&1

https://github.com/dprotopopov/tritumana/archive/master.zip
tritumana-master.zip
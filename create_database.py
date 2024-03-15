import mysql.connector
import json

with open('db_config.json') as json_file:
  db_config = json.load(json_file)

mydb = mysql.connector.connect(
    host=db_config['host'],
    user=db_config['user'],
    password=db_config['password'],
    database=db_config['database']
)

cursor = mydb.cursor()

cursor.execute('''
    CREATE TABLE IF NOT EXISTS `anzeigen` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `short` varchar(24) NOT NULL,
        `title` text NOT NULL,
        `description` text NOT NULL,
        `category_id` int(11) DEFAULT 1,
        `zustand_id` int(11) NOT NULL DEFAULT 1,
        `price` int(11) NOT NULL,
        `vb` bit(1) DEFAULT b'0',
        `versand` bit(1) DEFAULT b'0',
        `location_id` int(11) NOT NULL DEFAULT 1,
        `sold` bit(1) DEFAULT b'0',
        `ready` bit(1) DEFAULT b'0',
        `last_uploaded` timestamp NULL DEFAULT NULL,
        `online_until` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
''')

cursor.execute('''
    CREATE TABLE IF NOT EXISTS images (
        id INT AUTO_INCREMENT,
        file_name VARCHAR(256) NOT NULL,
        anzeige_id INT NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
''')

# street and street_nr will not be uploaded to kleinanzeigen.de
cursor.execute('''
    CREATE TABLE IF NOT EXISTS location (
        id INT AUTO_INCREMENT,
        short VARCHAR(24) NOT NULL,
        plz INT NOT NULL,
        street TEXT NOT NULL,
        street_nr INT NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
''')

cursor.execute('''
    CREATE TABLE IF NOT EXISTS zustand (
        id INT AUTO_INCREMENT,
        name TEXT NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
''')

cursor.execute('''
    CREATE TABLE IF NOT EXISTS category (
        id VARCHAR(256) NOT NULL,
        previous_id VARCHAR(256),
        name TEXT NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
''')


mydb.commit()

cursor.close()
mydb.close()

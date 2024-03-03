CREATE TABLE venue (
                       id INT(11) NOT NULL AUTO_INCREMENT,
                       name TEXT,
                       PRIMARY KEY (id)
)

CREATE TABLE space (
                       id INT(11) NOT NULL AUTO_INCREMENT,
                       venue INT(11) DEFAULT NULL,
                       name TEXT,
                       PRIMARY KEY (id)
)

CREATE TABLE event (
                       id INT(11) NOT NULL AUTO_INCREMENT,
                       space INT(11) DEFAULT NULL,
                       start MEDIUMTEXT,
                       duration INT(11) DEFAULT NULL,
                       name TEXT,
                       PRIMARY KEY (id)
)
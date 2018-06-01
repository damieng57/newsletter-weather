CREATE TABLE IF NOT EXISTS weather (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    code INT NOT NULL UNIQUE,
    comment VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS country (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    abbr VARCHAR(20) NOT NULL UNIQUE,
    countryfr VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS dayname (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    abbr VARCHAR(20) NOT NULL UNIQUE,
    dayfr VARCHAR(255) NOT NULL UNIQUE,
    dayen VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS iata (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    abbr VARCHAR(20) NOT NULL UNIQUE,
    airport VARCHAR(255) NOT NULL,
    cityid INT NOT NULL,
    FOREIGN KEY (cityid) REFERENCES city(id)
);

CREATE TABLE IF NOT EXISTS city (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    cityfr VARCHAR(255) NOT NULL,
    cityen VARCHAR(255) NOT NULL,
    countryid INT NOT NULL,
    FOREIGN KEY (countryid) REFERENCES country(id)
);

CREATE TABLE IF NOT EXISTS timedate (
    id INT NOT NULL AUTO_INCREMENT UNIQUE,
    checktime DATETIME NOT NULL,
    tempmax INT,
    tempmin INT,
    cityid INT NOT NULL,
    weatherid INT NOT NULL,
    dayid INT NOT NULL,
    PRIMARY KEY (checktime, cityid),
    FOREIGN KEY (cityid) REFERENCES city(id),
    FOREIGN KEY (weatherid) REFERENCES weather(id),
    FOREIGN KEY (dayid) REFERENCES dayname(id)
);

/* Ajout de valeurs pour la table weather - Code dispo voir API Yahoo */
INSERT INTO weather (code, comment) VALUES (0, "tornado");
INSERT INTO weather (code, comment) VALUES (1, "tropical_storm");
INSERT INTO weather (code, comment) VALUES (2, "hurricane");
INSERT INTO weather (code, comment) VALUES (3, "severe_thunderstorms");
INSERT INTO weather (code, comment) VALUES (4, "thunderstorms");
INSERT INTO weather (code, comment) VALUES (5, "mixed_rain_and_snow");
INSERT INTO weather (code, comment) VALUES (6, "mixed_rain_and_slee");
INSERT INTO weather (code, comment) VALUES (7, "mixed_snow_and_sleet");
INSERT INTO weather (code, comment) VALUES (8, "freezing_drizzle");
INSERT INTO weather (code, comment) VALUES (9, "freezing_rain");
INSERT INTO weather (code, comment) VALUES (10, "showers");
INSERT INTO weather (code, comment) VALUES (11, "showers");
INSERT INTO weather (code, comment) VALUES (12, "snow_flurries");
INSERT INTO weather (code, comment) VALUES (13, "light_snow_showers");


/* Ajout de valeurs pour la table country - Abbreviation suivant ISO 3166 */
INSERT INTO country (abbr, countryfr) VALUES ("fr", "France");
INSERT INTO country (abbr, countryfr) VALUES ("de", "Allemagne");
INSERT INTO country (abbr, countryfr) VALUES ("es", "Espagne");
INSERT INTO country (abbr, countryfr) VALUES ("it", "Italie");
INSERT INTO country (abbr, countryfr) VALUES ("be", "Belgique");
INSERT INTO country (abbr, countryfr) VALUES ("dk", "Danemark");
INSERT INTO country (abbr, countryfr) VALUES ("lu", "Luxembourg");
INSERT INTO country (abbr, countryfr) VALUES ("ie", "Irlande");
INSERT INTO country (abbr, countryfr) VALUES ("gb", "Royaume-Uni");
INSERT INTO country (abbr, countryfr) VALUES ("at", "Autriche");
INSERT INTO country (abbr, countryfr) VALUES ("pt", "Portugal");

/* Ajout de valeurs pour la table jour - Abbreviation en anglais */
INSERT INTO dayname (abbr, dayfr, dayen) VALUES ("Mon", "Lundi", "Monday");
INSERT INTO dayname (abbr, dayfr, dayen) VALUES ("Tue", "Mardi", "Tuesday");
INSERT INTO dayname (abbr, dayfr, dayen) VALUES ("Wed", "Mercredi", "Monday");
INSERT INTO dayname (abbr, dayfr, dayen) VALUES ("Thu", "Jeudi", "Thursday");
INSERT INTO dayname (abbr, dayfr, dayen) VALUES ("Fri", "Vendredi", "Friday");
INSERT INTO dayname (abbr, dayfr, dayen) VALUES ("Sat", "Samedi", "Saturday");
INSERT INTO dayname (abbr, dayfr, dayen) VALUES ("Sun", "Dimanche", "Sunday");

/* Ajout de valeurs pour la table ville - Abbreviation en anglais */
INSERT INTO dayname (abbr, dayfr, dayen) VALUES ("Mon", "Lundi", "Monday");
INSERT INTO dayname (abbr, dayfr, dayen) VALUES ("Tue", "Mardi", "Tuesday");
INSERT INTO dayname (abbr, dayfr, dayen) VALUES ("Wed", "Mercredi", "Wednesday");
INSERT INTO dayname (abbr, dayfr, dayen) VALUES ("Thu", "Jeudi", "Thursday");
INSERT INTO dayname (abbr, dayfr, dayen) VALUES ("Fri", "Vendredi", "Friday");
INSERT INTO dayname (abbr, dayfr, dayen) VALUES ("Sat", "Samedi", "Saturday");
INSERT INTO dayname (abbr, dayfr, dayen) VALUES ("Sun", "Dimanche", "Sunday");

/* Ajout de valeurs pour la table city*/
INSERT INTO city (cityfr, cityen, countryid) VALUES ("Londres", "London", 9);
INSERT INTO city (cityfr, cityen, countryid) VALUES ("Munich", "Munich", 2);
INSERT INTO city (cityfr, cityen, countryid) VALUES ("Milan", "Milan", 4);
INSERT INTO city (cityfr, cityen, countryid) VALUES ("Palma de Majorque", "Palma de Majorca", 3);
INSERT INTO city (cityfr, cityen, countryid) VALUES ("Berlin", "Berlin", 2);
INSERT INTO city (cityfr, cityen, countryid) VALUES ("Copenhague", "Copenhagen", 6);
INSERT INTO city (cityfr, cityen, countryid) VALUES ("Dublin", "Dublin", 8);
INSERT INTO city (cityfr, cityen, countryid) VALUES ("Porto", "Porto", 11);
INSERT INTO city (cityfr, cityen, countryid) VALUES ("Nice", "Nice", 1);
INSERT INTO city (cityfr, cityen, countryid) VALUES ("Vienne", "Vienna", 10);
INSERT INTO city (cityfr, cityen, countryid) VALUES ("Paris", "Paris", 1);

/* Ajout de valeurs pour la table iata - Abbreviation suivant ISO 3166-2 */
/* Nom des aéroports suivant site IATA */
INSERT INTO iata (abbr, airport, cityid) VALUES ("LCY", "City Airport", 1);
INSERT INTO iata (abbr, airport, cityid) VALUES ("MUC", "International", 2);
INSERT INTO iata (abbr, airport, cityid) VALUES ("MXP", "Malpensa", 3);
INSERT INTO iata (abbr, airport, cityid) VALUES ("PMI", "Palma de Mallorca", 4);
INSERT INTO iata (abbr, airport, cityid) VALUES ("TXL", "Tegel", 5);
INSERT INTO iata (abbr, airport, cityid) VALUES ("CPH", "Kastrup", 6);
INSERT INTO iata (abbr, airport, cityid) VALUES ("DUB", "International", 7);
INSERT INTO iata (abbr, airport, cityid) VALUES ("OPO", "Francisco Sa Carneiro", 8);
INSERT INTO iata (abbr, airport, cityid) VALUES ("NCE", "Cote d'Azur", 9);
INSERT INTO iata (abbr, airport, cityid) VALUES ("VIE", "Schwechat Intl", 10);


/* Ajout de date pour test */
INSERT INTO timedate (checktime, tempmax, tempmin, cityid, weatherid, dayid) VALUES ("2018-05-23", 23, 15, 2, 10, 4);
INSERT INTO timedate (checktime, tempmax, tempmin, cityid, weatherid, dayid) VALUES ("2018-05-24", 25, 14, 2, 9, 4);
INSERT INTO timedate (checktime, tempmax, tempmin, cityid, weatherid, dayid) VALUES ("2018-05-25", 30, 12, 2, 2, 4);
INSERT INTO timedate (checktime, tempmax, tempmin, cityid, weatherid, dayid) VALUES ("2018-05-26", 10, 5, 2, 5, 4);
INSERT INTO timedate (checktime, tempmax, tempmin, cityid, weatherid, dayid) VALUES ("2018-05-27", 20, 13, 2, 7, 4);
INSERT INTO timedate (checktime, tempmax, tempmin, cityid, weatherid, dayid) VALUES ("2018-05-28", 23, 10, 2, 7, 4);
INSERT INTO timedate (checktime, tempmax, tempmin, cityid, weatherid, dayid) VALUES ("2018-05-29", 26, 17, 2, 7, 4);
INSERT INTO timedate (checktime, tempmax, tempmin, cityid, weatherid, dayid) VALUES ("2018-05-30", 23, 15, 2, 10, 4);
INSERT INTO timedate (checktime, tempmax, tempmin, cityid, weatherid, dayid) VALUES ("2018-05-31", 25, 14, 2, 9, 4);
INSERT INTO timedate (checktime, tempmax, tempmin, cityid, weatherid, dayid) VALUES ("2018-06-01", 30, 12, 2, 2, 4);
INSERT INTO timedate (checktime, tempmax, tempmin, cityid, weatherid, dayid) VALUES ("2018-06-02", 10, 5, 2, 5, 4);

/* Exemple de requêtes
/* Obtenir la météo suivant le code IATA de l'aéroport sur les 3 prochains jours*/
/*

SELECT iata.abbr, city.cityfr, iata.airport, timedate.checktime, timedate.tempmax, timedate.tempmin, weather.comment
FROM city, iata, country, timedate, weather
WHERE iata.abbr =  "MUC"
AND timedate.checktime >= CURRENT_DATE()
AND iata.cityid = city.id
AND city.countryid = country.id
AND timedate.cityid = city.id
AND timedate.weatherid = weather.id
ORDER BY timedate.checktime ASC
LIMIT 0, 3

*/
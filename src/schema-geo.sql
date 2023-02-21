CREATE TABLE geopoint (
    start_ip INTEGER PRIMARY KEY,
    end_ip INTEGER NOT NULL UNIQUE, 
    country_code TEXT
);
----
CREATE INDEX start_end_ip_index ON geopoint (start_ip, end_ip);
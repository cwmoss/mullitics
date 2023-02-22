CREATE TABLE IF NOT EXISTS hits (
    Timestamp INTEGER NOT NULL,
    Session TEXT,
    URI TEXT,
    Ref TEXT,
    Country TEXT,
    Device TEXT,
    geoip_lookup INTEGER,
    width INTEGER
)
----
CREATE INDEX IF NOT EXISTS hits_timestamp on hits(Timestamp)
----
# insert or replace into salt (salt) values('A');
----
create table salt (
    id INTEGER not null unique check(id=1) default 1, 
    salt TEXT not null, 
    salted_at TEXT default (datetime('now', 'localtime'))
)

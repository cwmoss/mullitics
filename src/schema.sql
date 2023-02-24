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
create table if not exists salt (
    id INTEGER not null unique check(id=1) default 1, 
    salt TEXT not null, 
    salted_at TEXT default (datetime('now', 'localtime'))
)
----
#  
insert OR REPLACE into stats
SELECT 'path' as cat, strftime("%Y-%m-%d", Timestamp, "unixepoch", "localtime") as day , URI as val, count(URI) as total from hits group by day, val
----
create table if not exists stats (
    day TEXT,
    cat TEXT,
    val TEXT,
    total INTEGER,
    UNIQUE(day,cat,val)
)
----
CREATE INDEX stats_unique ON stats (day, cat, val)
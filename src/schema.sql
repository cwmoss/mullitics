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
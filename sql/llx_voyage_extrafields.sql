CREATE TABLE IF NOT EXISTS llx_voyage_extrafields
(
    rowid integer NOT NULL auto_increment PRIMARY KEY,
    tms date,
    fk_object int,
    import_key varchar(14)

)

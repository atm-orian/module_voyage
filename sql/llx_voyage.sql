CREATE TABLE IF NOT EXISTS llx_voyage
(
    rowid integer NOT NULL auto_increment PRIMARY KEY,
    reference varchar(100) NOT NULL,
    tarif int,
    pays varchar(100),
    fk_soc int,
    description text,
    date_deb timestamp,
    date_fin timestamp,
    date_creation date,
    tms date,
    entity int
)

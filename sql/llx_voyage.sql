CREATE TABLE IF NOT EXISTS llx_voyage
(
    rowid integer NOT NULL auto_increment PRIMARY KEY,
    reference varchar(100) NOT NULL,
    tarif int,
    pays varchar(100),
    fk_soc int,
    description text,
    date_deb date,
    date_fin date,
    date_creation date,
    tms date,
    entity int
)

ALTER TABLE llx_voyage_link ADD CONSTRAINT fk_voyage_voyage FOREIGN KEY (fk_voyage) REFERENCES llx_voyage (rowid);
ALTER TABLE llx_voyage_link ADD CONSTRAINT fk_voyage_tag FOREIGN KEY (fk_tag) REFERENCES llx_c_voyage_tag (rowid);
ALTER TABLE llx_voyage_link ADD CONSTRAINT pk_voyage_tag PRIMARY KEY (fk_voyage, fk_tag);


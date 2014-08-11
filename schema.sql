-- -- rensa databas
-- DROP TABLE padgroups;
-- DROP TABLE pads;
-- DROP TABLE ingroup;
-- DROP TABLE groups;
-- DROP TABLE users;

CREATE TABLE users (
	userid SERIAL PRIMARY KEY,
	username TEXT UNIQUE,
	password VARCHAR(64) NOT NULL,
	salt VARCHAR(64) NOT NULL,
	iterations INTEGER NOT NULL,
	algorithm TEXT NOT NULL
);

CREATE TABLE groups (
	groupid SERIAL PRIMARY KEY,
	groupname TEXT NOT NULL
);

CREATE TABLE ingroup (
	groupid INTEGER 
		REFERENCES groups (groupid) 
		ON UPDATE CASCADE 
		ON DELETE CASCADE,
	userid INTEGER 
		REFERENCES users (userid) 
		ON UPDATE CASCADE 
		ON DELETE CASCADE,
	PRIMARY KEY (groupid, userid)
);

CREATE TABLE pads (
	padid INTEGER PRIMARY KEY,
	padname TEXT NOT NULL,
	ownerownerid INTEGER 
		REFERENCES groups (groupid)
		ON UPDATE CASCADE 
		ON DELETE CASCADE
);

CREATE TABLE padgroups (
	padid INTEGER 
		REFERENCES pads (padid) 
		ON UPDATE CASCADE 
		ON DELETE CASCADE,
	groupid INTEGER 
		REFERENCES groups (groupid) 
		ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY (padid, groupid)
);


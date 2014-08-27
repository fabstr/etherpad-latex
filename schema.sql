CREATE TABLE users (
	id INTEGER AUTO_INCREMENT PRIMARY KEY NOT NULL,
	username VARCHAR(255) UNIQUE,
	password VARCHAR(255) NOT NULL,

	-- to store the remember token of laravel
	remember_token VARCHAR(100),

	-- to store these laravel values
	created_at TIMESTAMP,
	updated_at TIMESTAMP
);

CREATE TABLE groups (
	id INTEGER AUTO_INCREMENT PRIMARY KEY NOT NULL,
	groupname VARCHAR(255) UNIQUE,

	-- etherpad's corresponding group
	ethergroupname TEXT,

	-- the owner of the group
	user_id INTEGER
	REFERENCES users (id)
	ON UPDATE CASCADE
	ON DELETE CASCADE

	-- to store these laravel values
	created_at TIMESTAMP,
	updated_at TIMESTAMP
);

CREATE TABLE group_user (
	group_id INTEGER
	REFERENCES groups (id)
	ON UPDATE CASCADE
	ON DELETE CASCADE,
	user_id INTEGER
	REFERENCES users (id)
	ON UPDATE CASCADE
	ON DELETE CASCADE,
	PRIMARY KEY (group_id, user_id)
);

CREATE TABLE documents (
	id INTEGER AUTO_INCREMENT PRIMARY KEY NOT NULL,
	documentname TEXT NOT NULL,

	-- etherpad's corresponding document
	etherdocumentname TEXT,

	-- the owner of the document
	group_id INTEGER  
	REFERENCES groups (id)
	ON UPDATE CASCADE
	ON DELETE CASCADE,

	-- to store these laravel values
	created_at TIMESTAMP,
	updated_at TIMESTAMP
);

CREATE TABLE snippets (
	id INTEGER AUTO_INCREMENT PRIMARY KEY NOT NULL,
	snippetname TEXT NOT NULL,

	-- the content of the snippet
	content TEXT,

	-- the owner of the snippet
	user_id INTEGER 
	REFERENCES users (id)
	ON UPDATE CASCADE
	ON DELETE CASCADE,

	-- to store these laravel values
	created_at TIMESTAMP,
	updated_at TIMESTAMP
);


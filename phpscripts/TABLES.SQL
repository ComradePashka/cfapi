create table if not exists users (
	id int primary key auto_increment,
	name varchar(255) not null,
	pass varchar(255) not null,
	cfname varchar(253) not null,
	cfkey varchar(253) not null
);
create table if not exists zones (
	id varchar(253) primary key,
	userid int not null,
	zonename varchar(64),
	data varchar(253),
	foreign key (userid)
		references users(id)
);
create table if not exists records (
	id int primary key,
	zoneid varchar(253),
	name varchar(253),
	type varchar(4),
	data varchar(253),
	foreign key (zoneid)
		references zones(id)
);
/*
create table if not exists logs (
	userid key users.id
	data
);
create table if not exists audit (
	userid key users.id
);
*/

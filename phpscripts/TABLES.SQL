create database if not exists cfapi;

create table if not exists users (
	id int primary key auto_increment,
	name varchar(255) not null,
	pass varchar(255) not null,
	cfname varchar(253) not null,
	cfkey varchar(253) not null,
	default_ns varchar(253),
	default_a varchar(253),
	default_www varchar(253),
	default_mx varchar(253),
	default_wcard varchar(253),
	default_cname varchar(253)
);
create table if not exists zones (
	id varchar(253) primary key,
	userid int not null,
	zonename varchar(253)not null,
	status varchar(253) not null,
	sync boolean not null,
	foreign key (userid)
		references users(id)
);
create table if not exists records (
	zoneid varchar(253),
	name varchar(253),
	type varchar(5),
	data varchar(253),
	foreign key (zoneid)
		references zones(id)
	ON DELETE CASCADE ON UPDATE CASCADE,
	key basekey (zoneid, name, type)
);

create table if not exists logs (
	userid int not null,
	data varchar(512),
        foreign key (userid)
                 references users(id)

);

create table if not exists audit (
	userid int not null,
	data varchar(512),
        foreign key (userid)
                 references users(id)
);


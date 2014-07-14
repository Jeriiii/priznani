insert into users
values	(3, NULL, NULL, 1, 2650, 'user', DATE_ADD(CURRENT_TIMESTAMP(),INTERVAL 12 DAY), CURRENT_TIMESTAMP(), 'user@test.cz', 'Test User', '125d6d03b32c84d492747f79cf0bf6e179d287f341384eb5d6d3197525ad6be8e6df0116032935698f99a09e265073d1d6c32c274591bf1d0a20ad67cba921bc'),
			(4, NULL, NULL, 1, 8796, 'admin', DATE_ADD(CURRENT_TIMESTAMP(),INTERVAL 16 DAY), CURRENT_TIMESTAMP(), 'admin@test.cz', 'Test Admin', '125d6d03b32c84d492747f79cf0bf6e179d287f341384eb5d6d3197525ad6be8e6df0116032935698f99a09e265073d1d6c32c274591bf1d0a20ad67cba921bc');
			
			
insert into users_properties
values	(3, 'muž', 'sex', 'Oh bože, už budu.', 'Hledám zábavu a vzrušení.', 'free', 'hetero', '180', '5', 'abnormal', 'normal', 'sometimes', 'often', 'vš', 'b', 'black', 1, 0, 1, 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 0),
			(4, 'žena', 'sex', 'To je ale macek.', 'Moc ráda bych nějakýho svalouše co to umí v posteli.', 'free', 'hetero', '165', NULL, NULL, NULL, 'often', 'no', 'sos', 'c', 'blond', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
			
update users SET propertyID = 3 WHERE id = 3;

update users SET propertyID = 4 WHERE id = 4;
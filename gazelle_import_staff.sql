
-- set crozz and asimo to be fls
UPDATE `users_info` SET `SupportFor`='help and support' WHERE `UserID` IN ( 68969, 88669 );
-- promote bork
UPDATE `users_main` SET `PermissionID`='11' WHERE `ID` = 1246;
UPDATE `users_main` SET `PermissionID`='15' WHERE `ID` = 84499;

create database if not exists accounts;

use accounts;

create table if not exists accounts
(
    id int auto_increment not null,
    account_name text not null,
    amount int default 0 not null,
    constraint amounts_id_uindex
        unique (id)
);

delimiter //

drop trigger if exists update_trigger //
CREATE TRIGGER update_trigger
    BEFORE UPDATE
    ON accounts FOR EACH ROW
BEGIN
    IF NEW.amount < 0 THEN
        signal sqlstate '45000' set message_text = 'Invalid transferring';
    END IF;
END
//

delimiter ;

insert ignore into accounts (id, account_name, amount) values (1, 'First', 1000), (2, 'Second', 1000), (3, 'Third', 1000), (4, 'Another', 1000);


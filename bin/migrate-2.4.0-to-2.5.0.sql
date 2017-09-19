# Adding pseudo WWW record type
# Adding CAA record type

ALTER TABLE dns_record MODIFY TYPE ENUM('MX','NS','A','TXT','PTR','CNAME','DNAME','A6','AAAA','SUBNS','DELEGATE','SRV','WWW','CAA');

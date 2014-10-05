#!/usr/local/bin/python

import xmlrpclib, getopt, sys

params = \
{       "user"          : "username",
        "password"      : "password",
        "zone"          : "domain.name",
        "name"          : "hostname",
        "oldaddress"    : "*",
        "ttl"           : "600",
        "updatereverse" : "0",
}

server = "https://freedns.42.pl/xmlrpc.php"

def usage():
        print """
freedns-dyndns.py [-h|--help                      ] 
                  [-u|--user       <user>         ] 
                  [-p|--password   <password>     ] 
                  [-z|--zone       <zone>         ] 
                  [-r|--recordname <record name>  ] 
                  [-o|--oldaddress <old address>  ] 
                  [-n|--newaddress <new address>  ] 
                  [-t|--ttl        <ttl>          ] 
                  [--updatereverse <1|0>          ]
                  [-s|--server     <xmlrpc server>] 

Inserts can be performed by leaving "oldaddress" empty.
Deletes can be performed by leaving "newaddress" empty.
Updates are performed by giving both old and new addresses.
Old address can be wildcard '*'.
New address can be "<dynamic>", server will use IP you're connecting from.
Be careful about proxies with this!

                """

def main():
        global server, params
        opts, args = getopt.getopt(sys.argv[1:], "hu:p:z:r:o:n:s:t:", ["help", "user=", "password=","zone=", "recordname=", "oldaddress=", "newaddress=", "server=", "ttl="])
        for o, a in opts:
                if o in ("-u", "--user"):
                        params["user"] = a
                elif o in ("-p", "--password"):
                        params["password"] = a
                elif o in ("-z", "--zone"):
                        params["zone"] = a
                elif o in ("-r", "--recordname"):
                        params["name"] = a
                elif o in ("-o", "--oldaddress"):
                        params["oldaddress"] = a
                elif o in ("-n", "--newaddress"):
                        params["newaddress"] = a
                elif o in ("-s", "--server"):
                        server = a
                elif o in ("-t", "--ttl"):
                        params["ttl"] = a
                elif o in ("--updatereverse"):
                        params["updatereverse"] = a
                else:
                        usage()
                        sys.exit()

        # print "p: %s" % params
        client = xmlrpclib.Server(server)

        try:
          print "result: %s" % client.xname.updateArecord(params)
        except xmlrpclib.Fault, e:
          print e


if __name__ == "__main__":
        main()




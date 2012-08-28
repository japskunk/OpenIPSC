/*
dmrmon - a utility to monitor DMR repeater IP linking protocols
Copyright (C) 2012 David Kierzokwski (kd8eyf@digitalham.info)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA. */
#include<stdio.h>
#include<stdlib.h>
#include<string.h>
#include "/usr/include/pcap/pcap.h" 	// NEED TO FIX THIS
#include<sys/socket.h>
#include<arpa/inet.h>
#include<net/ethernet.h>
#include<netinet/udp.h>
#include<netinet/ip.h>
#include<getopt.h>
int  debug = 0;
char *devname = NULL;

void usage( int8_t e );
void processPacket(u_char *arg, const struct pcap_pkthdr* pkthdr, const u_char * packet)
{
	int i=0, *counter = (int *)arg;
	if (debug == 1){
		printf("Packet Count: %d\n", ++(*counter));
	        printf("Received Packet Size: %d\n", pkthdr->len);
	}
        for (i=0; i<pkthdr->len; i++) {
	        printf("%x", packet[i]);
	
}                        
printf("\n");
        return;
}

int main(int argc, char *argv[] )
{
	char packet_filter[] = "ip and udp";
	struct bpf_program fcode;
	u_int netmask;
	pcap_t *descr = NULL;
        int32_t c;
        while ((c = getopt(argc, argv, "dVhi:")) != EOF) {
                switch (c) {
                case 'd': 
			debug = 1;
			break;
		case 'V':
                        version();
                        break;
                case 'i':
                        devname = optarg;
                        break;
                case 'h':
                        usage(-1);
                        break;
                }
        }
        if (devname == NULL) {
                usage(-1);
        }
        if (debug == 1) {
		 printf("USING CAPTURE DEVICE: %s\n", devname); }

        pcap_if_t *alldevsp , *device;
        pcap_t *handle;
        char errbuf[100] , devs[100][100];
        int count = 1 , n;
        handle = pcap_open_live(devname , 65536 , 1 , 0 , errbuf);

        if (handle == NULL) {
                fprintf(stderr, "Couldn't open device %s : %s\n" , devname , errbuf);
                exit(1);
        }
        pcap_compile(handle, &fcode, packet_filter, 1, netmask);
	if ( pcap_loop(handle, -1, processPacket, (u_char *)&count) == -1) {
                fprintf(stderr, "ERROR: %s\n", pcap_geterr(descr) );
                exit(1);
        }


        return 0;
}
void usage(int8_t e)
{
        printf("dmrmon - The openIPSC DMR network monitor client\n"
               "         Used in congution with dmrserv\n"
	       "usage: dmrmon -i interface \n"
               "              -h is this help\n"
               "              -V version information\n"
               "	      -d debug information\n"
		"");
        exit(e);
}

int version ( void )
{
        printf ("dmrmon 0.01\n");
        exit(1);
}

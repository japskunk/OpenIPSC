/*
dmrmonitor-hytera - monitor hytera repeater and send to server
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
#include<time.h>	
struct UDP_hdr {
        u_short	uh_sport;				//Source Port
        u_short	uh_dport;				//Destnation Port
        u_short	uh_ulen;				//Datagram Length
        u_short	uh_sum;					//Datagram Checksum
};
int debug = 0;
char *devname = NULL;
uint16_t SrcID = ((uint16_t)67 << 8) | ((uint16_t)65 << 0);
void usage( int8_t e );
void processPacket(u_char *arg, const struct pcap_pkthdr* pkthdr, const u_char * packet)
{
        struct ip * ip;
        struct UDP_hdr * udp;
	struct slot{
		int Status;
		int CallType;
		int SourceID;
		int DestinationID;
	};
	struct repeater{
		struct ip RepeaterID;
		struct slot slot1;
		struct slot slot2;
	};
        unsigned int IP_header_length;
        unsigned int capture_len = pkthdr->len;
        int PacketType;
        long value;
        int i=0, *counter = (int *)arg;
	uint32_t DmrID = 0;
	uint16_t sync = 0;
	uint16_t Timeslot = 0;
	time_t Time;	
	uint32_t DestinationID;
	struct tm * tm;
	PacketType = 0; 
	packet += sizeof (struct ether_header);
        capture_len -= sizeof(struct ether_header);
        ip = (struct ip*) packet;
        IP_header_length = ip->ip_hl *4;
        packet += IP_header_length;
        capture_len -= IP_header_length;
        udp = (struct UDP_hdr*) packet;
        packet += sizeof (struct UDP_hdr);
        capture_len -= sizeof (struct UDP_hdr);
	Time = time(NULL);
	tm = gmtime (&Time);
	printdata();
	if ((capture_len == 72) && (debug != 2)) {
                PacketType = *(packet+8);
		sync  = *(packet+22)<<8|*(packet+23);
	        printf("PT: %i SYNC: %i\n",PacketType,sync);
		if (sync == 4369){
			if (Timeslot == 4369){ Timeslot = 1; };
                        if (Timeslot == 8738){ Timeslot = 2; };
			DmrID = *(packet+38)<<16|*(packet+40)<<8|*(packet+42);
                        DestinationID = *(packet+66)<<16|*(packet+65)<<8|*(packet+64);
                        RepeaterID = ip->ip_src;

			if PacketType = 2 {	//New or Continued Call
				repeater	
			}
			if PacketType = 3 {	//End Of Call
			}
			Timeslot = *(packet+16)<<8|*(packet+17);
			};
		}
		
        }


int main(int argc, char *argv[] )
{
        char packet_filter[] = "ip and udp";
        struct bpf_program fcode;
        u_int netmask;
        pcap_t *descr = NULL;
        int32_t c;
         while ((c = getopt(argc, argv, "opdVhi:")) != EOF) {
                switch (c) {
                case 'p':
                        debug = 2;
                        break;
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
                printf("USING CAPTURE DEVICE: %s\n", devname);
        }

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
        printf(	"Usage: DMRmontiorHytera [OPTION]... \n"
                "Listen send DMR data for remote server for processing\n"
                "\n"
		"   -i, --interface     Interface to listen on\n"
                "   -h, --help          This Help\n"
                "   -V, --version       Version Information\n"
                "   -d, --debug         Show whats happening in english\n"
                "   -p, --payload       Dump UDP payload data in one line hex (usefull for reverse engineering)\n"
                "\n"
                "Report cat bugs to kd8eyf@digitalham.info\n");
        exit(e);
}

int version ( void )
{
        printf ("hytera 0.04\n");
        exit(1);
}

int printdata ()
{
///	if (debug == 0) {
///		printf("%04d-%02d-%02d ",tm->tm_year+1900, tm->tm_mon+1, tm->tm_mday);
///	        printf("%02d:%02d:%02d ",tm->tm_hour, tm->tm_min, tm->tm_sec);
///        	printf("%s %i %i %i %i\n",inet_ntoa(ip->ip_src), PacketType, Timeslot,  DmrID, DestinationID);
///	}
///	if (debug == 1) {
///	}g
///	if (debug == 2) {
///                printf("%s",inet_ntoa(ip->ip_src));
///                printf(":%d -> ",ntohs(udp->uh_sport));
///                printf("%s", inet_ntoa(ip->ip_dst));
///                printf(":%d -> ",ntohs(udp->uh_dport));
///                while (i < capture_len) {
///                        printf("%02X", packet[i]);
///                        i++;
///               	}
///        	printf("\n");
///        }
///
}


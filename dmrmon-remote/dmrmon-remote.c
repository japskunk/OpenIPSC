/*
dmrmon-remote - monitor DMR repeater and send to server
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
struct UDP_hdr {
        u_short	uh_sport;				//Source Port
        u_short	uh_dport;				//Destnation Port
        u_short	uh_ulen;				//Datagram Length
        u_short	uh_sum;					//Datagram Checksum
};
int  debug = 0;
char *devname = NULL;

void usage( int8_t e );
void processPacket(u_char *arg, const struct pcap_pkthdr* pkthdr, const u_char * packet)
{
        struct ip * ip;
        struct UDP_hdr * udp;
        unsigned int IP_header_length;
        unsigned int capture_len = pkthdr->len;
        int isDMR=0, Role = 0,TS1Linked=0,TS2Linked=0;		// Role Peer = 0, Slave = 1
        char buffer[15];				// Used for temporay data conversions
        long PacketType,SourceID, UserID, DestinationID;
        char *packetDescription ="unknown";
        int i=0, *counter = (int *)arg;
        packet += sizeof (struct ether_header);
        capture_len -= sizeof(struct ether_header);
        ip = (struct ip*) packet;
        IP_header_length = ip->ip_hl *4;
        packet += IP_header_length;
        capture_len -= IP_header_length;
        udp = (struct UDP_hdr*) packet;
        packet += sizeof (struct UDP_hdr);
        capture_len -= sizeof (struct UDP_hdr);
        sprintf(buffer,"%x",*packet);
        PacketType = strtol(buffer,NULL,16);
        if ((capture_len >= 20) && (capture_len <= 21)) {	//Based on size we might have a motorola heartbeat packet
                if(((*packet) >= 150) && ((*packet)<=153)) {	//Check first offset to see if it really is
                        isDMR = 1;
                        switch(*packet) {			//Set the packet Description
                        case 150:
                                packetDescription = "Master Ping";
                                Role = 1;
                                break;
                        case 151:
                                packetDescription = "Master Ping Reply";
                                Role = 1;
                                break;
                        case 152:
                                packetDescription = "Peer Ping";
                                break;
                        case 153:
                                packetDescription = "Peer Ping Reply";
                                break;
                        }
                        sprintf(buffer,"%x",*packet);
                        PacketType = strtol(buffer,NULL,16);
                        sprintf(buffer,"%02x%02x%02x",*(packet+2),*(packet+3),*(packet+4));
                        SourceID = strtol(buffer,NULL,16);
                        sprintf(buffer,"%02x",*(packet+5));
                        //printf("%02x",*(packet+5));

                }
        }
        if (debug == 1) {
                uint32_t i=0, j=0;
                printf("\n\n\n");
                printf("Packet Count:\t%d\n", ++(*counter));
                printf("Packet Size:\t%d\n", capture_len);
                printf("Size ETH Header\t%lu\n",sizeof(struct ether_header));
                printf("Size IP Header\t%u\n",IP_header_length);
                printf("UDP Src Port\t%d\n", ntohs(udp->uh_sport));
                printf("IDP Dst Port\t%d\n", ntohs(udp->uh_dport));
                printf("Packet Type\t%lu\n",PacketType);
                printf("Packet Desc\t%s\n",packetDescription);
                printf("Soute Role\t%i\n",Role);
                printf("Source ID\t%lu\n",SourceID);
                printf("Slot1 Status\t%i\n",TS1Linked);
                printf("Slot2 Status\t%i\n",TS2Linked);
                printf("\n%04x ",j);
                while (i < capture_len) {
                        printf("%02x ", packet[i]);
                        i++;
                        j++;
                        if (j == 8) {
                                printf("  ");
                        }
                        if (j == 16) {
                                printf("\n%04x ",i);
                                j=0;
                        }
                }
        } else if (debug ==2) {
                while (i < capture_len) {
                        printf("%02X", packet[i]);
                        i++;
                }
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
        while ((c = getopt(argc, argv, "pdVhi:")) != EOF) {
                switch (c) {
                case 'd':
                        debug = 1;
                        break;
                case 'p':
                        debug = 2;
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
        printf(	"Usage: dmrmon-remote [OPTION]... [REMOTE SERVER]...\n"
                "Listen send DMR data for remote server for processing\n"
                "\n"
                "   -i, --interface		Interface to listen on\n"
                "   -h, --help		This Help\n"
                "   -V, --version		Version Information\n"
                "   -d, --debug		Show verbose information\n"
                "   -p, --payload  		Dump only UDP payload data in one line hex\n"
                "\n"
                "With no REMOTE SERVER or REMOTE SERVER is -, output to standard output\n"
                "\n"
                "Examples:\n"
                "   dmrmon-remote -i eth0 192.168.10.20 50000\n"
                "   Send the DMR data heard on eth0 to remote server\n"
                "\n"
                "Report cat bugs to kd8eyf@digitalham.info\n");
        exit(e);
}

int version ( void )
{
        printf ("dmrmon 0.01\n");
        exit(1);
}













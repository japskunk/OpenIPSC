/*	$NetBSD: print-ascii.c,v 1.1 1999/09/30 14:49:12 sjg Exp $ 	*/

/*-
 * Copyright (c) 1997, 1998 The NetBSD Foundation, Inc.
 * All rights reserved.
 *
 * This code is derived from software contributed to The NetBSD Foundation
 * by Alan Barrett and Simon J. Gerraty.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this software
 *    must display the following acknowledgement:
 *        This product includes software developed by the NetBSD
 *        Foundation, Inc. and its contributors.
 * 4. Neither the name of The NetBSD Foundation nor the names of its
 *    contributors may be used to endorse or promote products derived
 *    from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE NETBSD FOUNDATION, INC. AND CONTRIBUTORS
 * ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED
 * TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE FOUNDATION OR CONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#ifndef lint
static const char rcsid[] _U_ =
     "@(#) $Header: /tcpdump/master/tcpdump/print-ascii.c,v 1.16.2.1 2005/07/06 20:54:49 guy Exp $";
#endif
#include <tcpdump-stdinc.h>
#include <stdio.h>

#include "interface.h"

#define ASCII_LINELENGTH 300
#define HEXDUMP_BYTES_PER_LINE 16
#define HEXDUMP_SHORTS_PER_LINE (HEXDUMP_BYTES_PER_LINE / 2)
#define HEXDUMP_HEXSTUFF_PER_SHORT 5 /* 4 hex digits and a space */
#define HEXDUMP_HEXSTUFF_PER_LINE \
		(HEXDUMP_HEXSTUFF_PER_SHORT * HEXDUMP_SHORTS_PER_LINE)

void
ascii_print(register const u_char *cp, register u_int length)
{
	register int s;

	putchar('\n');
	while (length > 0) {
		s = *cp++;
		length--;
		if (!isgraph(s) &&
		    (s != '\t' && s != ' ' && s != '\n' && s != '\r'))
			putchar('.');
		else
			putchar(s);
	}
}

void
hex_and_ascii_print_with_offset(register const char *ident,
    register const u_char *cp, register u_int length, register u_int oset)
{
	register u_int i;
	register int s1, s2;
	register int nshorts;
	char hexstuff[HEXDUMP_SHORTS_PER_LINE*HEXDUMP_HEXSTUFF_PER_SHORT+1], *hsp;
	char asciistuff[ASCII_LINELENGTH+1], *asp;

	nshorts = length / sizeof(u_short);
	i = 0;
	hsp = hexstuff; asp = asciistuff;
	while (--nshorts >= 0) {
		s1 = *cp++;
		s2 = *cp++;
		(void)snprintf(hsp, sizeof(hexstuff) - (hsp - hexstuff),
		    " %02x%02x", s1, s2);
		hsp += HEXDUMP_HEXSTUFF_PER_SHORT;
		*(asp++) = (isgraph(s1) ? s1 : '.');
		*(asp++) = (isgraph(s2) ? s2 : '.');
		i++;
		if (i >= HEXDUMP_SHORTS_PER_LINE) {
			*hsp = *asp = '\0';
			(void)printf("%s0x%04x: %-*s  %s",
			    ident, oset, HEXDUMP_HEXSTUFF_PER_LINE,
			    hexstuff, asciistuff);
			i = 0; hsp = hexstuff; asp = asciistuff;
			oset += HEXDUMP_BYTES_PER_LINE;
		}
	}
	if (length & 1) {
		s1 = *cp++;
		(void)snprintf(hsp, sizeof(hexstuff) - (hsp - hexstuff),
		    " %02x", s1);
		hsp += 3;
		*(asp++) = (isgraph(s1) ? s1 : '.');
		++i;
	}
	if (i > 0) {
		*hsp = *asp = '\0';
		(void)printf("%s0x%04x: %-*s  %s",
		     ident, oset, HEXDUMP_HEXSTUFF_PER_LINE,
		     hexstuff, asciistuff);
	}
}

void
hex_and_ascii_print(register const char *ident, register const u_char *cp,
    register u_int length)
{
	hex_and_ascii_print_with_offset(ident, cp, length, 0);
}

/*
 * telnet_print() wants this.  It is essentially default_print_unaligned()
 */
void
hex_print_with_offset(register const char *ident, register const u_char *cp, register u_int length,
		      register u_int oset)
{
	register u_int i, s;
	register int nshorts;

	nshorts = (u_int) length / sizeof(u_short);
	i = 0;
	while (--nshorts >= 0) {
		if ((i++ % 8) == 0) {
			(void)printf("%s0x%04x: ", ident, oset);
			oset += HEXDUMP_BYTES_PER_LINE;
		}
		s = *cp++;
		(void)printf(" %02x%02x", s, *cp++);
	}
	if (length & 1) {
		if ((i % 8) == 0)
			(void)printf("%s0x%04x: ", ident, oset);
		(void)printf(" %02x", *cp);
	}
}

/*
 * just for completeness
 */
void
hex_print(register const char *ident, register const u_char *cp, register u_int length)
{
	hex_print_with_offset(ident, cp, length, 0);
}


/*DL5DI=>*/

/*
 * prepare DMR header for transmission to DMR-Monitor Server
 */
void
dmr_print(register const u_char *cp, register u_int length)
{
	register u_int i, s;
	register int nshorts;

	char buffer[10];
	long rid, srcid, destid, value;
	int j, port;
	time_t Time;
	struct tm * tm;
	extern int ts;
	extern long rptrid[2], rptrid0[2][MAXSTN];
	extern int seqnr[2], seqnr0[2][MAXSTN];
	extern time_t lh[MAXRPTR];
	extern long usrid[MAXRPTR];

	nshorts = (u_int) length / sizeof(u_short);
	i = 0;

	Time = time(NULL);
	tm = gmtime (&Time);
	sprintf(buffer,"%02x%02x",*(cp+22),*(cp+23));
	port = strtol(buffer,NULL,16);	/*DestPort*/

	cp += 28;

       if((*cp >= 150) && (*cp <= 153) && (length > 33)){
       /* Master keep-alive-request:  96h / 150 */
       /* Master keep-alive-response: 97h / 151 */
       /* Peer keep-alive-request:    98h / 152 */
       /* Peer keep-alive-response:   99h / 153 */

	    sprintf(buffer,"%02x%02x%02x%02x",*(cp+1),*(cp+2),*(cp+3),*(cp+4));
	    rid = strtol(buffer,NULL,16); /*RptrID*/

	    for(j = 0; j < MAXRPTR; j++){
			if((usrid[j] == rid) && (time(NULL) < lh[j] + 60)){
				j = MAXRPTR+1;	/* rptr was active within last 60 sec, no update required */
			}
	    }

	    if(j <= MAXRPTR){
			for(j = 0; j < (MAXRPTR-1); j++){
				usrid[j] = usrid[j+1];
				lh[j] = lh[j+1];
			}

			usrid[MAXRPTR-1] = rid;
			lh[MAXRPTR-1] = time(NULL);

			printf("%04d-%02d-%02d",tm->tm_year+1900, tm->tm_mon+1, tm->tm_mday);
			printf(" %02d:%02d:%02d",tm->tm_hour, tm->tm_min, tm->tm_sec);
			printf(" %d", port);

			/* oct 0-1: */
			(void)printf(" %02x",*cp); /*H: format id, L: A/GI*/

			/* oct 1-4: repeater-id: */
			(void)printf(" %ld",rid); /*RptrID*/

			/* oct 5 */
			(void)printf(" %02x\n",*(cp+5));
		}

	} else if((*cp >= 128) && (*cp < 133) && (length > 57)){		/* >80h <85h */

		/* header of voice frames */

		/* octed 17 / bit 32: timeslot */
		ts = *(cp+17) && 32;

		/* oct 1-4: repeater-id: */
		sprintf(buffer,"%02x%02x%02x%02x",*(cp+1),*(cp+2),*(cp+3),*(cp+4));
		rptrid[ts] = strtol(buffer,NULL,16); /*RptrID*/

		/* octed 5: sequence number: */
		sprintf(buffer,"%d",*(cp+5)); /*SeqNr*/
		seqnr[ts]=atoi(buffer);

		/* check fifo buffer of last 20 stations on this timeslot */
		for(j=0; j < MAXSTN; j++){
		    if((rptrid[ts] == rptrid0[ts][j]) && (seqnr[ts] == seqnr0[ts][j]))
			    j = MAXSTN+1; /* found */
		}

		if(j <= MAXSTN){
		    /* remove oldest and add new entry at the end of fifo buffer */
		    for(j=0; j < (MAXSTN-1); j++){
				rptrid0[ts][j] = rptrid0[ts][j+1];
				seqnr0[ts][j] = seqnr0[ts][j+1];
		    }
		    rptrid0[ts][MAXSTN-1] = rptrid[ts];
		    seqnr0[ts][MAXSTN-1] = seqnr[ts];
			
		    printf("%04d-%02d-%02d",tm->tm_year+1900, tm->tm_mon+1, tm->tm_mday);
		    printf(" %02d:%02d:%02d",tm->tm_hour, tm->tm_min, tm->tm_sec);
		    printf(" %d", port);

		    /* oct 0: */
		    (void)printf(" %02x",*cp); /*H: format id, L: A/GI*/

		    /* oct 6-8: source-id: */
		    sprintf(buffer,"%02x%02x%02x",*(cp+6),*(cp+7),*(cp+8));
		    srcid = strtol(buffer,NULL,16); /*SrcID*/

		    /* oct 9-11: destination-id: */
		    sprintf(buffer,"%02x%02x%02x",*(cp+9),*(cp+10),*(cp+11));
		    destid = strtol(buffer,NULL,16); /*DstID*/

		    (void)printf(" %ld",rptrid[ts]); /*RptrID*/
		    (void)printf(" %d",seqnr[ts]); /*SeqNr*/
		    (void)printf(" %ld",srcid); /*SrcID*/
		    (void)printf(" %ld",destid); /*DstID*/

		    /* oct 12: */
		    (void)printf(" %02x",*(cp+12)); /*prio - voice/data*/

		    /* oct 13-16: ctrl flags: */
		    (void)printf(" %02x%02x%02x%02x",*(cp+13),*(cp+14),*(cp+15),*(cp+16));

		    /* oct 17: CallControlInfo */
		    (void)printf(" %02x",*(cp+17));

		    /* oct 18: ContribSrcID */
		    (void)printf(" %02x",*(cp+18));

		    /* oct 19: PayloadType*/
		    (void)printf(" %02x",*(cp+19));

		    /* oct 20-21: sequence number: */
		    sprintf(buffer,"%02x%02x",*(cp+20),*(cp+21));
		    value = strtol(buffer,NULL,16); /*Sequence number*/
		    (void)printf(" %ld",value);

		    /* oct 22-25: timestamp: */
		    (void)printf(" %02x%02x%02x%02x",*(cp+22),*(cp+23),*(cp+24),*(cp+25));

		    /* oct 26-29: SyncSrcID: */
		    sprintf(buffer,"%02x%02x%02x%02x",*(cp+26),*(cp+27),*(cp+28),*(cp+29));
		    value = strtol(buffer,NULL,16);
		    (void)printf(" %ld",value);

		    /* oct 30: DataTypeVoiceHeader */
		    (void)printf(" %02x",*(cp+30));

		    /* oct 31:  RSSI/threshold and parity values */
		    (void)printf(" %02x",*(cp+31));

		    /* oct 32-33: */
		    sprintf(buffer,"%02x%02x",*(cp+32),*(cp+33));
		    value = strtol(buffer,NULL,16); /* length to follow (words) */
		    (void)printf(" %ld",value);

		    /* oct 34:  RSSI status */
		    (void)printf(" %02x",*(cp+34));

		    /* oct 35:  slot type / Sync */
		    (void)printf(" %02x",*(cp+35));

		    /* oct 36-37: */
		    sprintf(buffer,"%02x%02x",*(cp+36),*(cp+37));
		    value = strtol(buffer,NULL,16); /* data size */
		    (void)printf(" %ld",value);

		    (void)printf("\r\n");
		}
	}
}

/*<=DL5DI*/


#ifdef MAIN
int
main(int argc, char *argv[])
{
	hex_print("\n\t", "Hello, World!\n", 14);
	printf("\n");
	hex_and_ascii_print("\n\t", "Hello, World!\n", 14);
	printf("\n");
	ascii_print("Hello, World!\n", 14);
	printf("\n");
#define TMSG "Now is the winter of our discontent...\n"
	hex_print_with_offset("\n\t", TMSG, sizeof(TMSG) - 1, 0x100);
	printf("\n");
	hex_and_ascii_print_with_offset("\n\t", TMSG, sizeof(TMSG) - 1, 0x100);
	printf("\n");
	exit(0);
}
#endif /* MAIN */

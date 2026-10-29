# Team 51 notes

Our working copy of the ROSSA site, so the team can read and edit the code.
The original is Brian's repo (uconndxlab/rossa, private). Changes that should go live go to him.

## Running it
The easiest place is the team VM, where a demo copy is already running:
1. UConn VPN on.
2. `ssh -N -L 8001:127.0.0.1:8001 yourNetID@sdp51rossaai.soc.uconn.edu`
3. Open http://localhost:8001 (ask Luiz for the demo login).

Running it on your own laptop needs PHP 8.3, Composer, Node 20+ and Postgres 17 (see README.md).

## Known issues in the JSON ingester (app/Http/Controllers/JsonIngester.php)
- status was 'New', which crashed every upload (fixed in this repo)
- country is saved as text but the table uses country_id, so it gets dropped
- every investigator needs an email; papers usually give only one
- animal models are not ingested yet

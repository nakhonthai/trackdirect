# Upgrading to this Modified Trackdirect Code

**To use this updated code you must be running at least Postgres v12 or later!**

This release adds a column to the packet database table(s) for storing the "to callsign" of the station a packet is addressed to. In order to use that  A more logical place for this would be in the packet parsing code (python) but since this is a WORM database (Write Once Read Many) a generated column adds no measurable overhead and add the benefit of making this field available for all currently stored data.

If you have a lot of stored packet records this query might take a long time.  This query will alter the main packet tables as well as any of the child tables (i.e. packet20220926).

```
ALTER TABLE packet ADD COLUMN "to_call" text GENERATED ALWAYS AS ("substring"(raw, '::(.*?) :'::text)) STORED
```

This new column is used in queries therefore it is also recommended to create indexes on that column.  Indexes do not propagate to inherited tables so this step will need to be performed to every packet table stored in your database.

```
create index packet_to_call_idx on packet(to_call)
```

Then repeat for each inherited packet table...

```
create index packet20220926_to_call_idx on packet(to_call)
```

Once all indexes have completed, the upgrade is complete.

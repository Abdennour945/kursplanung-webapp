--
-- PostgreSQL database dump
--

\restrict dA4jCmy3ojpaTcYBj9jS1THxMFxa2jqKECHDCeKMA8Syqx88JnqfWcPxXbdd2Fx

-- Dumped from database version 14.20 (Homebrew)
-- Dumped by pg_dump version 14.20 (Homebrew)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: arbeitszeitmodell; Type: TABLE; Schema: public; Owner: abdennour
--

CREATE TABLE public.arbeitszeitmodell (
    arbeitszeitmodell_id integer NOT NULL,
    bezeichnung character varying(50) NOT NULL,
    max_wochenstunden integer NOT NULL,
    max_kursstunden integer NOT NULL
);


ALTER TABLE public.arbeitszeitmodell OWNER TO abdennour;

--
-- Name: arbeitszeitmodell_arbeitszeitmodell_id_seq; Type: SEQUENCE; Schema: public; Owner: abdennour
--

CREATE SEQUENCE public.arbeitszeitmodell_arbeitszeitmodell_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.arbeitszeitmodell_arbeitszeitmodell_id_seq OWNER TO abdennour;

--
-- Name: arbeitszeitmodell_arbeitszeitmodell_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: abdennour
--

ALTER SEQUENCE public.arbeitszeitmodell_arbeitszeitmodell_id_seq OWNED BY public.arbeitszeitmodell.arbeitszeitmodell_id;


--
-- Name: buero; Type: TABLE; Schema: public; Owner: abdennour
--

CREATE TABLE public.buero (
    buero_id integer NOT NULL,
    name character varying(100) NOT NULL,
    nummer character varying(50),
    standort_id integer
);


ALTER TABLE public.buero OWNER TO abdennour;

--
-- Name: buero_buero_id_seq; Type: SEQUENCE; Schema: public; Owner: abdennour
--

CREATE SEQUENCE public.buero_buero_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.buero_buero_id_seq OWNER TO abdennour;

--
-- Name: buero_buero_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: abdennour
--

ALTER SEQUENCE public.buero_buero_id_seq OWNED BY public.buero.buero_id;


--
-- Name: krankmeldung; Type: TABLE; Schema: public; Owner: abdennour
--

CREATE TABLE public.krankmeldung (
    krankmeldung_id integer NOT NULL,
    trainer_id integer NOT NULL,
    datum_von date NOT NULL,
    datum_bis date NOT NULL,
    status character varying(30) DEFAULT 'aktiv'::character varying
);


ALTER TABLE public.krankmeldung OWNER TO abdennour;

--
-- Name: krankmeldung_krankmeldung_id_seq; Type: SEQUENCE; Schema: public; Owner: abdennour
--

CREATE SEQUENCE public.krankmeldung_krankmeldung_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.krankmeldung_krankmeldung_id_seq OWNER TO abdennour;

--
-- Name: krankmeldung_krankmeldung_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: abdennour
--

ALTER SEQUENCE public.krankmeldung_krankmeldung_id_seq OWNED BY public.krankmeldung.krankmeldung_id;


--
-- Name: kunde; Type: TABLE; Schema: public; Owner: abdennour
--

CREATE TABLE public.kunde (
    kunde_id integer NOT NULL,
    name character varying(100) NOT NULL,
    email character varying(120),
    status character varying(30) DEFAULT 'aktiv'::character varying
);


ALTER TABLE public.kunde OWNER TO abdennour;

--
-- Name: kunde_kunde_id_seq; Type: SEQUENCE; Schema: public; Owner: abdennour
--

CREATE SEQUENCE public.kunde_kunde_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.kunde_kunde_id_seq OWNER TO abdennour;

--
-- Name: kunde_kunde_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: abdennour
--

ALTER SEQUENCE public.kunde_kunde_id_seq OWNED BY public.kunde.kunde_id;


--
-- Name: kurs; Type: TABLE; Schema: public; Owner: abdennour
--

CREATE TABLE public.kurs (
    kurs_id integer NOT NULL,
    datum date NOT NULL,
    uhrzeit time without time zone NOT NULL,
    dauer_minuten integer DEFAULT 60 NOT NULL,
    raum_id integer NOT NULL,
    trainer_id integer NOT NULL,
    ersatztrainer_id integer,
    status character varying(30) DEFAULT 'normal'::character varying,
    CONSTRAINT kurs_dauer_check CHECK ((dauer_minuten = 60))
);


ALTER TABLE public.kurs OWNER TO abdennour;

--
-- Name: kurs_kunde; Type: TABLE; Schema: public; Owner: abdennour
--

CREATE TABLE public.kurs_kunde (
    kurs_id integer NOT NULL,
    kunde_id integer NOT NULL
);


ALTER TABLE public.kurs_kunde OWNER TO abdennour;

--
-- Name: kurs_kurs_id_seq; Type: SEQUENCE; Schema: public; Owner: abdennour
--

CREATE SEQUENCE public.kurs_kurs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.kurs_kurs_id_seq OWNER TO abdennour;

--
-- Name: kurs_kurs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: abdennour
--

ALTER SEQUENCE public.kurs_kurs_id_seq OWNED BY public.kurs.kurs_id;


--
-- Name: mitarbeiter; Type: TABLE; Schema: public; Owner: abdennour
--

CREATE TABLE public.mitarbeiter (
    mitarbeiter_id integer NOT NULL,
    name character varying(100) NOT NULL,
    email character varying(120) NOT NULL,
    passwort character varying(255) NOT NULL,
    rolle character varying(30) NOT NULL,
    arbeitszeitmodell_id integer,
    status character varying(30) DEFAULT 'aktiv'::character varying,
    buero_id integer,
    standort_id integer,
    CONSTRAINT mitarbeiter_rolle_check CHECK (((rolle)::text = ANY ((ARRAY['Sekretariat'::character varying, 'Trainer'::character varying, 'Administrator'::character varying])::text[])))
);


ALTER TABLE public.mitarbeiter OWNER TO abdennour;

--
-- Name: mitarbeiter_mitarbeiter_id_seq; Type: SEQUENCE; Schema: public; Owner: abdennour
--

CREATE SEQUENCE public.mitarbeiter_mitarbeiter_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mitarbeiter_mitarbeiter_id_seq OWNER TO abdennour;

--
-- Name: mitarbeiter_mitarbeiter_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: abdennour
--

ALTER SEQUENCE public.mitarbeiter_mitarbeiter_id_seq OWNED BY public.mitarbeiter.mitarbeiter_id;


--
-- Name: raum; Type: TABLE; Schema: public; Owner: abdennour
--

CREATE TABLE public.raum (
    raum_id integer NOT NULL,
    name character varying(100) NOT NULL,
    nummer character varying(50),
    kapazitaet integer,
    standort_id integer NOT NULL
);


ALTER TABLE public.raum OWNER TO abdennour;

--
-- Name: raum_raum_id_seq; Type: SEQUENCE; Schema: public; Owner: abdennour
--

CREATE SEQUENCE public.raum_raum_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.raum_raum_id_seq OWNER TO abdennour;

--
-- Name: raum_raum_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: abdennour
--

ALTER SEQUENCE public.raum_raum_id_seq OWNED BY public.raum.raum_id;


--
-- Name: standort; Type: TABLE; Schema: public; Owner: abdennour
--

CREATE TABLE public.standort (
    standort_id integer NOT NULL,
    name character varying(100) NOT NULL,
    adresse character varying(255)
);


ALTER TABLE public.standort OWNER TO abdennour;

--
-- Name: standort_standort_id_seq; Type: SEQUENCE; Schema: public; Owner: abdennour
--

CREATE SEQUENCE public.standort_standort_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.standort_standort_id_seq OWNER TO abdennour;

--
-- Name: standort_standort_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: abdennour
--

ALTER SEQUENCE public.standort_standort_id_seq OWNED BY public.standort.standort_id;


--
-- Name: arbeitszeitmodell arbeitszeitmodell_id; Type: DEFAULT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.arbeitszeitmodell ALTER COLUMN arbeitszeitmodell_id SET DEFAULT nextval('public.arbeitszeitmodell_arbeitszeitmodell_id_seq'::regclass);


--
-- Name: buero buero_id; Type: DEFAULT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.buero ALTER COLUMN buero_id SET DEFAULT nextval('public.buero_buero_id_seq'::regclass);


--
-- Name: krankmeldung krankmeldung_id; Type: DEFAULT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.krankmeldung ALTER COLUMN krankmeldung_id SET DEFAULT nextval('public.krankmeldung_krankmeldung_id_seq'::regclass);


--
-- Name: kunde kunde_id; Type: DEFAULT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.kunde ALTER COLUMN kunde_id SET DEFAULT nextval('public.kunde_kunde_id_seq'::regclass);


--
-- Name: kurs kurs_id; Type: DEFAULT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.kurs ALTER COLUMN kurs_id SET DEFAULT nextval('public.kurs_kurs_id_seq'::regclass);


--
-- Name: mitarbeiter mitarbeiter_id; Type: DEFAULT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.mitarbeiter ALTER COLUMN mitarbeiter_id SET DEFAULT nextval('public.mitarbeiter_mitarbeiter_id_seq'::regclass);


--
-- Name: raum raum_id; Type: DEFAULT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.raum ALTER COLUMN raum_id SET DEFAULT nextval('public.raum_raum_id_seq'::regclass);


--
-- Name: standort standort_id; Type: DEFAULT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.standort ALTER COLUMN standort_id SET DEFAULT nextval('public.standort_standort_id_seq'::regclass);


--
-- Data for Name: arbeitszeitmodell; Type: TABLE DATA; Schema: public; Owner: abdennour
--

COPY public.arbeitszeitmodell (arbeitszeitmodell_id, bezeichnung, max_wochenstunden, max_kursstunden) FROM stdin;
1	Vollzeit	40	20
2	Teilzeit	20	10
\.


--
-- Data for Name: buero; Type: TABLE DATA; Schema: public; Owner: abdennour
--

COPY public.buero (buero_id, name, nummer, standort_id) FROM stdin;
1	Büro Fes	B-101	1
2	Büro Krefeld	B-201	2
3	Büro Dortmund	B-301	3
4	Büro Casablanca	B-401	4
\.


--
-- Data for Name: krankmeldung; Type: TABLE DATA; Schema: public; Owner: abdennour
--

COPY public.krankmeldung (krankmeldung_id, trainer_id, datum_von, datum_bis, status) FROM stdin;
10	18	2026-06-28	2026-06-28	aktiv
\.


--
-- Data for Name: kunde; Type: TABLE DATA; Schema: public; Owner: abdennour
--

COPY public.kunde (kunde_id, name, email, status) FROM stdin;
\.


--
-- Data for Name: kurs; Type: TABLE DATA; Schema: public; Owner: abdennour
--

COPY public.kurs (kurs_id, datum, uhrzeit, dauer_minuten, raum_id, trainer_id, ersatztrainer_id, status) FROM stdin;
48	2026-06-27	08:00:00	60	4	13	\N	normal
50	2026-06-28	08:00:00	60	1	18	14	vertretung
\.


--
-- Data for Name: kurs_kunde; Type: TABLE DATA; Schema: public; Owner: abdennour
--

COPY public.kurs_kunde (kurs_id, kunde_id) FROM stdin;
\.


--
-- Data for Name: mitarbeiter; Type: TABLE DATA; Schema: public; Owner: abdennour
--

COPY public.mitarbeiter (mitarbeiter_id, name, email, passwort, rolle, arbeitszeitmodell_id, status, buero_id, standort_id) FROM stdin;
8	Admin 2	admin2@test.de	$2y$12$1qqdPl8ddQb6.pCixNAzW.vIS364apNQYEMr7cXfdLNHd.bYoQDCu	Administrator	1	aktiv	\N	\N
11	loay	sekretariat3@test.de	$2y$12$1qqdPl8ddQb6.pCixNAzW.vIS364apNQYEMr7cXfdLNHd.bYoQDCu	Sekretariat	2	aktiv	4	\N
2	ANASS BENAMI	sekretariat@test.de	$2y$12$S7GNlviauZ10REgEcB6QjOG283BsI7JmfK6CeGt7Lx/93QMBSfuoK	Sekretariat	1	aktiv	1	\N
14	Trainer 3	trainer3Vollzeit@test.de	$2y$12$1qqdPl8ddQb6.pCixNAzW.vIS364apNQYEMr7cXfdLNHd.bYoQDCu	Trainer	1	aktiv	\N	1
18	Trainer 7	trainer7Teilzeit@test.de	$2y$12$1qqdPl8ddQb6.pCixNAzW.vIS364apNQYEMr7cXfdLNHd.bYoQDCu	Trainer	2	aktiv	\N	1
20	Trainer 9	trainer9Teilzeit@test.de	$2y$12$1qqdPl8ddQb6.pCixNAzW.vIS364apNQYEMr7cXfdLNHd.bYoQDCu	Trainer	2	aktiv	\N	1
15	Trainer 4	trainer4Vollzeit@test.de	$2y$12$1qqdPl8ddQb6.pCixNAzW.vIS364apNQYEMr7cXfdLNHd.bYoQDCu	Trainer	1	aktiv	\N	2
16	Trainer 5	trainer5Vollzeit@test.de	$2y$12$1qqdPl8ddQb6.pCixNAzW.vIS364apNQYEMr7cXfdLNHd.bYoQDCu	Trainer	1	aktiv	\N	2
17	Trainer 6	trainer6Teilzeit@test.de	$2y$12$1qqdPl8ddQb6.pCixNAzW.vIS364apNQYEMr7cXfdLNHd.bYoQDCu	Trainer	2	aktiv	\N	2
13	Trainer 2	trainer2Vollzeit@test.de	$2y$12$1qqdPl8ddQb6.pCixNAzW.vIS364apNQYEMr7cXfdLNHd.bYoQDCu	Trainer	1	aktiv	\N	3
19	Trainer 8	trainer8Teilzeit@test.de	$2y$12$1qqdPl8ddQb6.pCixNAzW.vIS364apNQYEMr7cXfdLNHd.bYoQDCu	Trainer	2	aktiv	\N	3
12	Trainer 1	trainer1Vollzeit@test.de	$2y$12$1qqdPl8ddQb6.pCixNAzW.vIS364apNQYEMr7cXfdLNHd.bYoQDCu	Trainer	1	aktiv	\N	4
21	Trainer 10	trainer10Teilzeit@test.de	$2y$12$1qqdPl8ddQb6.pCixNAzW.vIS364apNQYEMr7cXfdLNHd.bYoQDCu	Trainer	2	aktiv	\N	4
1	admin	admin@test.de	$2y$12$S7GNlviauZ10REgEcB6QjOG283BsI7JmfK6CeGt7Lx/93QMBSfuoK	Administrator	1	aktiv	\N	\N
37	trainer19	trainer19@test.de	$2y$12$Phr8bska0WVXUK0G9TNT3.PaLkfcwI7LNAg0oGHKiAV8Lgz3cPYqa	Trainer	2	aktiv	\N	1
10	abdennor BENCHARDA	sekretariat2@test.de	$2y$12$1qqdPl8ddQb6.pCixNAzW.vIS364apNQYEMr7cXfdLNHd.bYoQDCu	Sekretariat	1	aktiv	4	\N
\.


--
-- Data for Name: raum; Type: TABLE DATA; Schema: public; Owner: abdennour
--

COPY public.raum (raum_id, name, nummer, kapazitaet, standort_id) FROM stdin;
1	Raum 101	101	20	1
2	Raum 102	102	25	1
3	Raum 201	201	20	2
4	Raum 301	301	30	3
5	Raum 401	401	30	4
\.


--
-- Data for Name: standort; Type: TABLE DATA; Schema: public; Owner: abdennour
--

COPY public.standort (standort_id, name, adresse) FROM stdin;
1	Fes	Fes, Marokko
2	Krefeld	Krefeld, Deutschland
3	Dortmund	Dortmund, Deutschland
4	Casablanca	Casablanca, Marokko
\.


--
-- Name: arbeitszeitmodell_arbeitszeitmodell_id_seq; Type: SEQUENCE SET; Schema: public; Owner: abdennour
--

SELECT pg_catalog.setval('public.arbeitszeitmodell_arbeitszeitmodell_id_seq', 2, true);


--
-- Name: buero_buero_id_seq; Type: SEQUENCE SET; Schema: public; Owner: abdennour
--

SELECT pg_catalog.setval('public.buero_buero_id_seq', 4, true);


--
-- Name: krankmeldung_krankmeldung_id_seq; Type: SEQUENCE SET; Schema: public; Owner: abdennour
--

SELECT pg_catalog.setval('public.krankmeldung_krankmeldung_id_seq', 10, true);


--
-- Name: kunde_kunde_id_seq; Type: SEQUENCE SET; Schema: public; Owner: abdennour
--

SELECT pg_catalog.setval('public.kunde_kunde_id_seq', 1, false);


--
-- Name: kurs_kurs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: abdennour
--

SELECT pg_catalog.setval('public.kurs_kurs_id_seq', 50, true);


--
-- Name: mitarbeiter_mitarbeiter_id_seq; Type: SEQUENCE SET; Schema: public; Owner: abdennour
--

SELECT pg_catalog.setval('public.mitarbeiter_mitarbeiter_id_seq', 37, true);


--
-- Name: raum_raum_id_seq; Type: SEQUENCE SET; Schema: public; Owner: abdennour
--

SELECT pg_catalog.setval('public.raum_raum_id_seq', 5, true);


--
-- Name: standort_standort_id_seq; Type: SEQUENCE SET; Schema: public; Owner: abdennour
--

SELECT pg_catalog.setval('public.standort_standort_id_seq', 4, true);


--
-- Name: arbeitszeitmodell arbeitszeitmodell_pkey; Type: CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.arbeitszeitmodell
    ADD CONSTRAINT arbeitszeitmodell_pkey PRIMARY KEY (arbeitszeitmodell_id);


--
-- Name: buero buero_pkey; Type: CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.buero
    ADD CONSTRAINT buero_pkey PRIMARY KEY (buero_id);


--
-- Name: krankmeldung krankmeldung_pkey; Type: CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.krankmeldung
    ADD CONSTRAINT krankmeldung_pkey PRIMARY KEY (krankmeldung_id);


--
-- Name: kunde kunde_pkey; Type: CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.kunde
    ADD CONSTRAINT kunde_pkey PRIMARY KEY (kunde_id);


--
-- Name: kurs_kunde kurs_kunde_pkey; Type: CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.kurs_kunde
    ADD CONSTRAINT kurs_kunde_pkey PRIMARY KEY (kurs_id, kunde_id);


--
-- Name: kurs kurs_pkey; Type: CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.kurs
    ADD CONSTRAINT kurs_pkey PRIMARY KEY (kurs_id);


--
-- Name: mitarbeiter mitarbeiter_email_key; Type: CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.mitarbeiter
    ADD CONSTRAINT mitarbeiter_email_key UNIQUE (email);


--
-- Name: mitarbeiter mitarbeiter_pkey; Type: CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.mitarbeiter
    ADD CONSTRAINT mitarbeiter_pkey PRIMARY KEY (mitarbeiter_id);


--
-- Name: raum raum_pkey; Type: CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.raum
    ADD CONSTRAINT raum_pkey PRIMARY KEY (raum_id);


--
-- Name: standort standort_pkey; Type: CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.standort
    ADD CONSTRAINT standort_pkey PRIMARY KEY (standort_id);


--
-- Name: kurs unique_raum_zeit; Type: CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.kurs
    ADD CONSTRAINT unique_raum_zeit UNIQUE (datum, uhrzeit, raum_id);


--
-- Name: buero buero_standort_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.buero
    ADD CONSTRAINT buero_standort_id_fkey FOREIGN KEY (standort_id) REFERENCES public.standort(standort_id);


--
-- Name: krankmeldung krankmeldung_trainer_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.krankmeldung
    ADD CONSTRAINT krankmeldung_trainer_id_fkey FOREIGN KEY (trainer_id) REFERENCES public.mitarbeiter(mitarbeiter_id);


--
-- Name: kurs kurs_ersatztrainer_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.kurs
    ADD CONSTRAINT kurs_ersatztrainer_id_fkey FOREIGN KEY (ersatztrainer_id) REFERENCES public.mitarbeiter(mitarbeiter_id);


--
-- Name: kurs_kunde kurs_kunde_kunde_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.kurs_kunde
    ADD CONSTRAINT kurs_kunde_kunde_id_fkey FOREIGN KEY (kunde_id) REFERENCES public.kunde(kunde_id) ON DELETE CASCADE;


--
-- Name: kurs_kunde kurs_kunde_kurs_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.kurs_kunde
    ADD CONSTRAINT kurs_kunde_kurs_id_fkey FOREIGN KEY (kurs_id) REFERENCES public.kurs(kurs_id) ON DELETE CASCADE;


--
-- Name: kurs kurs_raum_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.kurs
    ADD CONSTRAINT kurs_raum_id_fkey FOREIGN KEY (raum_id) REFERENCES public.raum(raum_id);


--
-- Name: kurs kurs_trainer_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.kurs
    ADD CONSTRAINT kurs_trainer_id_fkey FOREIGN KEY (trainer_id) REFERENCES public.mitarbeiter(mitarbeiter_id);


--
-- Name: mitarbeiter mitarbeiter_arbeitszeitmodell_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.mitarbeiter
    ADD CONSTRAINT mitarbeiter_arbeitszeitmodell_id_fkey FOREIGN KEY (arbeitszeitmodell_id) REFERENCES public.arbeitszeitmodell(arbeitszeitmodell_id);


--
-- Name: mitarbeiter mitarbeiter_buero_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.mitarbeiter
    ADD CONSTRAINT mitarbeiter_buero_id_fkey FOREIGN KEY (buero_id) REFERENCES public.buero(buero_id);


--
-- Name: mitarbeiter mitarbeiter_standort_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.mitarbeiter
    ADD CONSTRAINT mitarbeiter_standort_id_fkey FOREIGN KEY (standort_id) REFERENCES public.standort(standort_id);


--
-- Name: raum raum_standort_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: abdennour
--

ALTER TABLE ONLY public.raum
    ADD CONSTRAINT raum_standort_id_fkey FOREIGN KEY (standort_id) REFERENCES public.standort(standort_id);


--
-- PostgreSQL database dump complete
--

\unrestrict dA4jCmy3ojpaTcYBj9jS1THxMFxa2jqKECHDCeKMA8Syqx88JnqfWcPxXbdd2Fx


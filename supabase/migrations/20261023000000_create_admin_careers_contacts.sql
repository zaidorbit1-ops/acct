create table if not exists public.contact_submissions (
    id uuid primary key default gen_random_uuid(),
    name text not null check (char_length(name) between 2 and 100),
    company text not null default '' check (char_length(company) <= 150),
    email text not null check (char_length(email) <= 254),
    phone text not null check (char_length(phone) between 7 and 40),
    service text not null check (char_length(service) between 1 and 80),
    message text not null check (char_length(message) between 10 and 3000),
    privacy_accepted boolean not null default false check (privacy_accepted),
    source_ip_hash text,
    created_at timestamptz not null default now()
);

create index if not exists contact_submissions_created_at_idx
    on public.contact_submissions (created_at desc);
create index if not exists contact_submissions_rate_limit_idx
    on public.contact_submissions (source_ip_hash, created_at desc)
    where source_ip_hash is not null;

create table if not exists public.career_jobs (
    id uuid primary key default gen_random_uuid(),
    title text not null check (char_length(title) between 2 and 150),
    slug text not null unique check (char_length(slug) between 1 and 150),
    location text not null default '' check (char_length(location) <= 120),
    employment_type text not null default '' check (char_length(employment_type) <= 80),
    summary text not null default '' check (char_length(summary) <= 500),
    description text not null default '' check (char_length(description) <= 10000),
    requirements text not null default '' check (char_length(requirements) <= 10000),
    status text not null default 'draft' check (status in ('draft', 'open', 'closed')),
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists career_jobs_public_listing_idx
    on public.career_jobs (status, created_at desc);

create table if not exists public.admin_users (
    user_id uuid primary key references auth.users (id) on delete cascade,
    email text not null,
    is_active boolean not null default true,
    created_at timestamptz not null default now(),
    created_by uuid references auth.users (id) on delete set null
);

create unique index if not exists admin_users_email_lower_idx
    on public.admin_users (lower(email));

create or replace function public.set_career_job_updated_at()
returns trigger
language plpgsql
as $$
begin
    new.updated_at = now();
    return new;
end;
$$;

drop trigger if exists career_jobs_set_updated_at on public.career_jobs;
create trigger career_jobs_set_updated_at
    before update on public.career_jobs
    for each row execute function public.set_career_job_updated_at();

alter table public.contact_submissions enable row level security;
alter table public.career_jobs enable row level security;
alter table public.admin_users enable row level security;

revoke all on table public.contact_submissions from anon, authenticated;
revoke all on table public.career_jobs from anon, authenticated;
revoke all on table public.admin_users from anon, authenticated;
grant all on table public.contact_submissions to service_role;
grant all on table public.career_jobs to service_role;
grant all on table public.admin_users to service_role;

revoke all on function public.set_career_job_updated_at() from public, anon, authenticated;
grant execute on function public.set_career_job_updated_at() to service_role;

-- Bootstrap the first administrator after creating that user in Supabase Auth:
-- insert into public.admin_users (user_id, email)
-- select id, email from auth.users where lower(email) = lower('ADMIN_EMAIL_HERE')
-- on conflict (user_id) do update set email = excluded.email, is_active = true;

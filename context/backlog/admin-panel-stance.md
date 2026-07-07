# Admin panel stance

**Idea:** when a project needs an admin area, the default answer is **Filament**
alongside the Inertia frontend (they coexist cleanly — Filament owns `/admin`,
Inertia owns the public site). Building a bespoke React admin doubles the token
cost for CRUD screens Filament generates.

**Open questions:** does the template pre-wire Filament (cost: bigger dependency
surface for sites that never need it) or stay a documented decision? Current
lean: documented decision only; graduate to a feature when 2+ real projects
needed it.

=== My Calculators ===
Contributors: 1myadvisers
Donate link: https://myadvisers.in
Tags: financial calculator, emi calculator, sip calculator, loan calculator, credit score
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

All-in-one financial toolkit for India — 40 calculators across Bank & Loans, Post Office & Govt. Savings, Mutual Funds, Retirement, Tax, Insurance, Bonds, and General categories, plus a credit score eligibility checker, all under one settings dashboard.

== Description ==

**My Calculators** combines two toolkits under a single plugin and a single "My Calculators" admin menu:

**1. Financial Calculators** — embed interactive calculators on any page via shortcode, organized into categories:

*Bank & Loans*
* EMI Calculator
* Home Loan EMI Calculator
* Home Loan Eligibility Calculator
* Fixed Deposit — TDR (Interest Payout)
* Fixed Deposit — STDR (Cumulative)
* Recurring Deposit Calculator

*Post Office & Govt. Savings*
* PPF Calculator
* Sukanya Samriddhi Account Calculator
* Senior Citizens Savings Scheme Calculator
* Kisan Vikas Patra Calculator
* Monthly Income Scheme Calculator
* Post Office Time Deposit Calculator
* National Savings Certificate Calculator

*Mutual Funds*
* SIP Calculator
* ELSS Calculator
* SWP Calculator
* Lumpsum Investment Calculator

*Retirement*
* Retirement Corpus Calculator
* NPS Calculator
* EPF Calculator
* VPF Calculator
* Atal Pension Yojana Calculator
* PM-SYM Calculator
* PM Vaya Vandana Yojana Calculator
* Gratuity Calculator

*Tax*
* Income Tax Calculator (Old &amp; New Regime)
* Capital Gains Tax Calculator

*Insurance*
* Life Insurance Maturity Calculator
* Term Life Policy Calculator (Human Life Value)
* Endowment Policy Return Calculator
* Postal Life Insurance (PLI) Estimator
* Rural PLI (RPLI) Estimator
* PM Jeevan Jyoti Bima Yojana Overview
* PM Suraksha Bima Yojana Overview

*Bonds*
* Floating Rate Savings Bonds Calculator
* Sovereign Gold Bond Calculator
* 54EC Capital Gains Bonds Calculator

*General*
* Compound Interest Calculator
* Simple Interest Calculator
* Inflation Calculator

Use `[my_calculator]` to show every enabled calculator in a category-tabbed switcher, or `[my_calculator type="sip"]` to embed just one.

All math runs client-side in vanilla JS with HTML5 Canvas charts — no external calls, no third-party libraries.

**2. Credit Score Checker** — a `[credit_score_checker]` shortcode that collects name/PAN/mobile/DOB/PIN + mandatory consent, calls a credit bureau or aggregator API you configure, and shows the score with an eligibility band (Excellent/Good/Fair/Poor/Very Poor) and suggested product categories.

= Getting bureau access as a publisher =

Direct bureau access (CIBIL, Experian, Equifax, CRIF) requires being a
"Specified User" under the CIC Regulation Act — banks, NBFCs, SEBI/IRDA-
regulated entities. As a financial blog, two practical routes:

1. **API aggregators** — Surepass, Deepvue, Decentro, Setu, Karza. Self-
   serve developer signup, business KYC, sandbox + production keys,
   pay-per-pull pricing. Configure their credentials under **My
   Calculators → Credit Score**, and adapt `includes/credit/class-csc-api.php`
   to their exact field names (documented in each provider's API docs).
2. **Affiliate/widget partners** — Paisabazaar, BankBazaar, CRED, OneScore.
   Embed their existing free-score widget via their partner program
   instead of pulling bureau data yourself — no bureau agreement needed,
   they carry the compliance burden.

Sandbox mode is on by default and returns a deterministic fake score so
you can test the UI safely before connecting a real provider.

= Disclaimer =

Calculator results are estimates for illustrative and educational purposes only, using standard simplified financial formulas. Credit score bands are illustrative eligibility guidance, not a lending decision. Neither constitutes financial, investment, insurance, tax, or credit advice — always confirm with your bank, fund house, insurer, or lender.

== Installation ==

1. Upload the `my-calculators` folder to `/wp-content/plugins/`, or install via **Plugins → Add New → Upload Plugin**.
2. Activate through the **Plugins** menu.
3. Go to **My Calculators → Calculators** to enable individual calculators, set default rates, brand color, and currency symbol.
4. Go to **My Calculators → Credit Score** to configure your bureau/aggregator API credentials, eligibility thresholds, and consent text.
5. Add shortcodes to any page:
   * `[my_calculator type="emi"]` — a specific calculator (see the settings page for every slug)
   * `[my_calculator]` — a tabbed switcher with every enabled calculator
   * `[credit_score_checker]` — the credit score / eligibility form

== Frequently Asked Questions ==

= How do I show a specific calculator? =

Use `[my_calculator type="slug"]`, replacing `slug` with one of: `emi`, `sip`, `ppf`, `fd`, `rd`, `life-insurance`, `annuity`, `retirement`, `term-life`, `endowment`, `home-loan-emi`, `home-loan-eligibility`, `lumpsum`. Every shortcode is also listed on the settings page.

= Does the credit score checker call any external service? =

Yes — unlike the calculators (which run entirely client-side), the credit checker calls whichever bureau/aggregator API you configure under **My Calculators → Credit Score**. In sandbox mode (default) no real API call is made; a simulated score is returned instead.

= Can I disable calculators or the credit checker I don't need? =

Yes. Untick any calculator on the Calculators settings page. To disable the credit checker entirely, simply don't add the `[credit_score_checker]` shortcode to any page — its assets only load on pages that use it.

= What data does the credit checker store? =

Only SHA-256 hashes of PAN/mobile (used for daily rate-limiting) and the numeric score/date — never the raw PAN, mobile number, or full credit report.

== Changelog ==

= 1.2.0 =
* Fixed a display bug where every calculator in a category could appear stacked on the page at once instead of just the selected one.
* Redesigned the `[my_calculator]` switcher: category tabs at the top, then a dropdown scoped to that category's calculators — selecting one shows only that calculator.
* Hardened the "only one calculator visible" behavior with both an inline style and a `!important` CSS rule, so it holds even under aggressive page caching.

= 1.1.0 =
* Expanded from 13 to 40 calculators, adding Post Office & Govt. Savings (SSA, SCSS, KVP, MIS, Time Deposit, NSC), Mutual Funds (ELSS, SWP), Retirement (NPS, EPF, VPF, Atal Pension Yojana, PM-SYM, PM Vaya Vandana Yojana, Gratuity), Tax (Income Tax with Old/New regime, Capital Gains), Insurance (PLI, RPLI, PMJJBY, PMSBY), Bonds (Floating Rate Savings Bonds, Sovereign Gold Bond, 54EC), and General (Compound Interest, Simple Interest, Inflation).
* Split Fixed Deposit into TDR (interest payout) and STDR (cumulative) variants to match how banks actually offer them.
* Reorganized the `[my_calculator]` switcher into category tabs (Bank & Loans, Post Office & Govt. Savings, Mutual Funds, Retirement, Tax, Insurance, Bonds, General) instead of one long flat list.
* Redesigned the front-end styling: refreshed color system, highlighted primary result, polished cards/buttons/charts, better mobile spacing.
* Reorganized the settings page into card sections with rates grouped by category.

= 1.0.0 =
* Merged the standalone "My Calculator" and "My Credits" plugins into one package with a unified "My Calculators" admin menu, shared text domain, and cross-linked settings pages.

== Upgrade Notice ==

= 1.1.0 =
Adds 27 new calculators and a redesigned interface. Existing shortcodes and saved settings continue to work unchanged.

= 1.0.0 =
First release of the merged plugin. If you previously ran "My Calculator" and/or "My Credits" separately, deactivate and delete those, then install this package — your saved settings (option names are unchanged) will carry over automatically.

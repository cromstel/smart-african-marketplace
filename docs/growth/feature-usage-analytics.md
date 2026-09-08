# Feature Usage Analytics Baseline

This baseline defines minimum analytics coverage to track product adoption.

## Core Events
- User sign up and login success
- Vendor onboarding completion
- Product create/update/publish
- Add to cart and checkout started/completed
- Subscription started/renewed/canceled
- Booking created/confirmed/canceled

## Dashboard Minimums
- DAU/WAU trend
- Funnel: visit -> product view -> add to cart -> checkout complete
- Vendor activity: products published per week
- Subscription retention and churn
- Booking completion rate

## Data Quality Rules
- Use consistent event names and required properties.
- Include actor role (`customer`, `vendor`, `admin`) where relevant.
- Capture timestamps in UTC.
- Validate new events in staging before release.

## Monthly Review
1. Review top-used and underused features.
2. Identify drop-off points in key funnels.
3. Create action items for UX/product improvements.
4. Track changes versus prior month baseline.


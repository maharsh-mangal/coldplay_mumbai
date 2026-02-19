# Architecture & Tradeoffs

This document outlines key architectural decisions made for the Coldplay Mumbai ticket booking platform, along with their tradeoffs and alternatives considered.

---

## 1. Seat Locking Strategy

### Decision: Database-level pessimistic locking with `lockForUpdate()`

```php
$seats = Seat::whereIn('id', $seatIds)
    ->where('status', SeatStatus::Available)
    ->lockForUpdate()
    ->get();
```

### Why

- **Consistency:** Guarantees no two users can lock the same seat simultaneously
- **Simplicity:** Uses MySQL's built-in row-level locking
- **Atomic:** Combined with transactions, ensures all-or-nothing seat reservation

### Tradeoffs

| Pros | Cons |
|------|------|
| Strong consistency | Blocks concurrent queries on same rows |
| Simple implementation | Potential deadlocks under extreme load |
| No external dependencies | Doesn't scale horizontally easily |

### Alternatives Considered

1. **Redis distributed locks (Redlock)**
    - Better for horizontal scaling
    - Added complexity and potential for race conditions during network partitions
    - Chose not to use: overkill for expected traffic

2. **Optimistic locking with version column**
    - No blocking, retry on conflict
    - Poor UX with high contention (many retries)
    - Chose not to use: concert tickets have extremely high contention

3. **Queue-based seat allocation**
    - All requests go through a single queue
    - Guarantees ordering but adds latency
    - Chose not to use: users expect instant feedback

---

## 2. Session & Cache Store

### Decision: Redis for session, cache, and queue

### Why

- **Speed:** In-memory storage, sub-millisecond reads
- **Atomic operations:** Supports locks, counters, pub/sub
- **Unified:** One service for multiple concerns
- **Queue visibility:** Can inspect pending jobs easily

### Tradeoffs

| Pros | Cons |
|------|------|
| Fast session reads | Additional infrastructure |
| Shared sessions across instances | Data loss if Redis crashes (mitigated by persistence) |
| Built-in TTL for seat locks | Memory-bound storage |

### Alternatives Considered

1. **Database sessions (MySQL)**
    - Simpler setup, persistent by default
    - Slower reads, more DB load
    - Acceptable for low traffic, not for concert launches

2. **File-based sessions**
    - Zero dependencies
    - Can't scale horizontally
    - Not suitable for containerized deployments

---

## 3. Frontend Checkout Flow

### Decision: Multi-step modal with real-time status indicators

```
[Check Availability] → [Lock Seats] → [Redirect to Payment]
```

### Why

- **Transparency:** Users see exactly what's happening
- **Error recovery:** Clear feedback when seats become unavailable
- **Perceived performance:** Progress indicators reduce anxiety during high-traffic moments

### Tradeoffs

| Pros | Cons |
|------|------|
| Great UX during failures | More complex frontend code |
| Automatic seat map refresh on error | Multiple API calls vs single submit |
| Builds trust with users | Slight increase in total checkout time |

### Alternatives Considered

1. **Single form submission with Inertia**
    - Simpler, standard Laravel approach
    - Poor feedback during processing
    - Error handling less graceful

2. **WebSocket-based real-time updates**
    - Best possible UX
    - Significant infrastructure complexity
    - Chose not to use: HTTP polling/fetch sufficient for this use case

---

## 4. Seat Lock Expiration

### Decision: Database column with scheduled job cleanup

```php
// Seat has locked_until timestamp
// Scheduled job releases expired locks every minute
```

### Why

- **Simplicity:** No complex distributed state
- **Reliability:** Database is source of truth
- **Recoverable:** If queue dies, next run cleans up

### Tradeoffs

| Pros | Cons |
|------|------|
| Simple to implement | Up to 1-minute delay in releasing seats |
| Easy to debug | Scheduled job must run reliably |
| Works without Redis | Slight DB overhead |

### Alternatives Considered

1. **Redis TTL-based expiration**
    - Automatic cleanup via key expiration
    - Requires keeping DB and Redis in sync
    - Risk of inconsistency

2. **Event-driven expiration (delayed job per lock)**
    - Precise timing
    - Many jobs for many locks
    - Queue congestion during high traffic

---

## What I Would Add With More Time

- **User dashboard with header navigation** — Allow logged-in users to view their purchased tickets, booking history, and download e-tickets from a dedicated "My Tickets" page accessible via the main navigation header.

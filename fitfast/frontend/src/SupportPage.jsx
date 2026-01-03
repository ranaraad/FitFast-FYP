import { useEffect, useMemo, useState } from "react";
import api from "./api";

const statusCopy = {
  open: "Open",
  in_progress: "In Progress",
  resolved: "Resolved",
  closed: "Closed",
};

const typeCopy = {
  question: "Question",
  complaint: "Complaint",
  suggestion: "Suggestion",
  technical: "Technical",
};

export default function SupportPage() {
  const [tickets, setTickets] = useState([]);
  const [activeTicket, setActiveTicket] = useState(null);
  const [conversation, setConversation] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [newTicket, setNewTicket] = useState({ type: "question", message: "" });
  const [reply, setReply] = useState("");

  const sortedTickets = useMemo(() => {
    return [...tickets].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  }, [tickets]);

  useEffect(() => {
    fetchTickets();
  }, []);

  async function fetchTickets() {
    setError("");
    try {
      const res = await api.get("/chat-support");
      setTickets(res.data.data || []);
    } catch (err) {
      console.error(err);
      setError("Unable to load your support tickets. Please try again.");
    }
  }

  async function fetchConversation(ticketId) {
    setLoading(true);
    setError("");
    setSuccess("");
    try {
      const res = await api.get(`/chat-support/${ticketId}`);
      setActiveTicket(res.data.ticket);
      setConversation(res.data.conversation || []);
    } catch (err) {
      console.error(err);
      setError("We couldn't open this conversation. Please retry.");
    } finally {
      setLoading(false);
    }
  }

  async function handleCreateTicket(e) {
    e.preventDefault();
    setError("");
    setSuccess("");
    try {
      const res = await api.post("/chat-support", newTicket);
      setSuccess(res.data.message || "Ticket created successfully.");
      setNewTicket({ type: "question", message: "" });
      await fetchTickets();
    } catch (err) {
      console.error(err);
      setError("Please provide a valid message to start a chat.");
    }
  }

  async function handleSendReply(e) {
    e.preventDefault();
    if (!activeTicket) return;
    setError("");
    setSuccess("");
    try {
      const res = await api.post(`/chat-support/${activeTicket.id}/reply`, {
        message: reply,
      });
      setReply("");
      setSuccess(res.data.message || "Reply sent.");
      await fetchConversation(activeTicket.id);
    } catch (err) {
      console.error(err);
      const message = err.response?.data?.message || "Unable to send reply.";
      setError(message);
    }
  }

  return (
    <div className="support-page">
      <header className="support-hero">
        <div>
          <p className="eyebrow">We are here to help</p>
          <h1>Support chat</h1>
          <p className="muted">
            Ask questions, report issues, or share ideas. Our support team will
            follow up quickly.
          </p>
        </div>
        <div className="support-meta">
          <div className="pill">Live help desk</div>
          <div className="pill ghost">Average reply &lt; 1h</div>
        </div>
      </header>

      <div className="support-layout">
        <section className="ticket-list">
          <div className="section-heading">
            <h2>Your tickets</h2>
            <button className="refresh" onClick={fetchTickets}>
              ↻ Refresh
            </button>
          </div>

          {sortedTickets.length === 0 && (
            <p className="muted small">You have not opened any tickets yet.</p>
          )}

          <ul>
            {sortedTickets.map((ticket) => (
              <li
                key={ticket.id}
                className={
                  activeTicket?.id === ticket.id ? "ticket active" : "ticket"
                }
                onClick={() => fetchConversation(ticket.id)}
              >
                <div className="ticket-main">
                  <div className="ticket-type">{typeCopy[ticket.type]}</div>
                  <p className="ticket-message">{ticket.message}</p>
                </div>
                <div className={`badge ${ticket.status}`}>{statusCopy[ticket.status]}</div>
              </li>
            ))}
          </ul>
        </section>

        <section className="conversation-panel">
          {error && <div className="banner error">{error}</div>}
          {success && <div className="banner success">{success}</div>}

          {activeTicket ? (
            <>
              <div className="conversation-header">
                <div>
                  <p className="eyebrow">Ticket #{activeTicket.id}</p>
                  <h3>{typeCopy[activeTicket.type]}</h3>
                  <p className="muted small">
                    {statusCopy[activeTicket.status]} • Opened on{" "}
                    {new Date(activeTicket.created_at).toLocaleDateString()}
                  </p>
                </div>
                <div className={`badge ${activeTicket.status}`}>
                  {statusCopy[activeTicket.status]}
                </div>
              </div>

              <div className="conversation-body">
                {loading ? (
                  <p className="muted">Loading conversation...</p>
                ) : (
                  conversation.map((message) => (
                    <article
                      key={message.id}
                      className={
                        message.admin_id ? "message admin" : "message user"
                      }
                    >
                      <div className="message-meta">
                        <span className="pill ghost">
                          {message.admin_id ? "Support" : "You"}
                        </span>
                        <span className="muted small">
                          {new Date(message.created_at).toLocaleString()}
                        </span>
                      </div>
                      <p>{message.message}</p>
                    </article>
                  ))
                )}
              </div>

              <form className="composer" onSubmit={handleSendReply}>
                <label>Reply</label>
                <textarea
                  rows="3"
                  value={reply}
                  onChange={(e) => setReply(e.target.value)}
                  placeholder="Type your response"
                  required
                />
                <button type="submit" disabled={!reply.trim()}>
                  Send reply
                </button>
              </form>
            </>
          ) : (
            <div className="empty-state">
              <h3>Start a conversation</h3>
              <p className="muted">
                Pick a topic and share details so we can connect you with the
                right person.
              </p>

              <form className="new-ticket" onSubmit={handleCreateTicket}>
               
                <div className="type-grid">
                  {Object.entries(typeCopy).map(([value, label]) => (
                    <button
                      key={value}
                      type="button"
                      className={
                        newTicket.type === value ? "chip active" : "chip"
                      }
                      onClick={() => setNewTicket({ ...newTicket, type: value })}
                    >
                      {label}
                    </button>
                  ))}
                </div>

                <label>Your message</label>
                <textarea
                  rows="4"
                  value={newTicket.message}
                  onChange={(e) =>
                    setNewTicket({ ...newTicket, message: e.target.value })
                  }
                  placeholder="How can we help?"
                  required
                />

                <button type="submit" disabled={!newTicket.message.trim()}>
                  Send to support
                </button>
              </form>
            </div>
          )}
        </section>
      </div>
    </div>
  );
}
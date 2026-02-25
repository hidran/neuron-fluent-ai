"use client";

import { useEffect, useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import Link from "next/link";

interface ReadingSession {
  id: number;
  generated_text: string;
  created_at: string;
  pronunciation_score: number;
  intonation_score: number;
  grammar_score: number;
}

export default function PracticeHub() {
  const [sessions, setSessions] = useState<ReadingSession[]>([]);

  useEffect(() => {
    const fetchSessions = async () => {
      const response = await fetch("http://localhost:8000/api/reading-sessions");
      const data = await response.json();
      setSessions(data.data);
    };

    fetchSessions();
  }, []);

  return (
    <div className="p-24">
      <h1 className="text-2xl font-bold mb-4">Practice Hub</h1>
      <div className="space-y-4">
        {sessions.map((session) => (
          <Link href={`/practice-hub/${session.id}`} key={session.id}>
            <Card>
              <CardHeader>
                <CardTitle>Session from {new Date(session.created_at).toLocaleDateString()}</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="truncate">{session.generated_text}</p>
                <div className="flex justify-around mt-4">
                  <div>Pronunciation: {session.pronunciation_score}%</div>
                  <div>Intonation: {session.intonation_score}%</div>
                  <div>Grammar: {session.grammar_score}%</div>
                </div>
              </CardContent>
            </Card>
          </Link>
        ))}
      </div>
    </div>
  );
}

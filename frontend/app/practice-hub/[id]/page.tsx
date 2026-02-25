"use client";

import { useEffect, useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { notFound } from "next/navigation";

interface ReadingSession {
  id: number;
  generated_text: string;
  created_at: string;
  pronunciation_score: number;
  intonation_score: number;
  grammar_score: number;
  ai_feedback: string;
  recording: {
    audio_file_path: string;
  };
}

export default function SessionDetails({ params }: { params: { id: string } }) {
  const [session, setSession] = useState<ReadingSession | null>(null);

  useEffect(() => {
    const fetchSession = async () => {
      const response = await fetch(`http://localhost:8000/api/reading-sessions/${params.id}`);
      if (!response.ok) {
        return notFound();
      }
      const data = await response.json();
      setSession(data);
    };

    fetchSession();
  }, [params.id]);

  if (!session) {
    return <div>Loading...</div>;
  }

  return (
    <div className="p-24">
      <Card>
        <CardHeader>
          <CardTitle>Session from {new Date(session.created_at).toLocaleDateString()}</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div>
            <h2 className="font-bold">Text</h2>
            <p>{session.generated_text}</p>
          </div>
          <div>
            <h2 className="font-bold">Scores</h2>
            <div className="flex justify-around mt-2">
              <div>Pronunciation: {session.pronunciation_score}%</div>
              <div>Intonation: {session.intonation_score}%</div>
              <div>Grammar: {session.grammar_score}%</div>
            </div>
          </div>
          <div>
            <h2 className="font-bold">Feedback</h2>
            <p>{session.ai_feedback}</p>
          </div>
          <div>
            <h2 className="font-bold">Recording</h2>
            <audio src={`http://localhost:8000/storage/${session.recording.audio_file_path}`} controls />
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

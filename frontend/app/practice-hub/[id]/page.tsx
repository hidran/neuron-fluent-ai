"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useParams } from "next/navigation";
import { Card, CardHeader, CardTitle, CardContent, CardDescription, CardFooter } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { ArrowLeft, Calendar, Globe, Mic, CheckCircle, Info, Play, FileText, BarChart3, Sparkles } from "lucide-react";
import { cn } from "@/lib/utils";

interface ReadingRecording {
  id: number;
  audio_file_path: string;
  pronunciation_score: number;
  intonation_score: number;
  grammar_score: number;
  ai_feedback: string;
  storage_disk: string;
}

interface ReadingSession {
  id: number;
  language: string;
  generated_text: string;
  ai_audio_path: string; // Dedicated field for AI voice
  audio_file_path: string; // Latest user recording
  pronunciation_score: number;
  intonation_score: number;
  grammar_score: number;
  ai_feedback: string;
  created_at: string;
  reading_category: {
    name: string;
  };
  recordings: ReadingRecording[];
}

export default function SessionDetailsPage() {
  const { id } = useParams();
  const [session, setSession] = useState<ReadingSession | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchSession = async () => {
      try {
        const response = await fetch(`http://127.0.0.1:8000/api/reading-sessions/${id}`);
        const data = await response.json();
        setSession(data);
      } catch (error) {
        console.error("Failed to fetch session:", error);
      } finally {
        setIsLoading(false);
      }
    };
    fetchSession();
  }, [id]);

  if (isLoading) {
    return (
      <main className="container mx-auto py-12 px-4">
        <div className="flex items-center justify-center min-h-[400px]">
          <p className="text-muted-foreground animate-pulse">Loading session data...</p>
        </div>
      </main>
    );
  }

  if (!session) {
    return (
      <main className="container mx-auto py-12 px-4 text-center">
        <h1 className="text-2xl font-bold">Session not found</h1>
        <Button asChild className="mt-4" variant="outline">
          <Link href="/practice-hub">Back to History</Link>
        </Button>
      </main>
    );
  }

  const getAudioUrl = (recording: ReadingRecording) => {
    if (recording.storage_disk === 'public') {
      return `http://127.0.0.1:8000/storage/${recording.audio_file_path.replace(/^\//, '')}`;
    }
    return recording.audio_file_path; // Fallback if disk is different
  };

  const getAiAudioUrl = () => {
    if (!session.ai_audio_path) return null;
    return `http://127.0.0.1:8000/storage/${session.ai_audio_path.replace(/^\//, '')}`;
  };

  return (
    <main className="container mx-auto py-12 px-4 max-w-4xl animate-in fade-in duration-700">
      <div className="space-y-8">
        <div className="flex items-center justify-between">
          <Button variant="ghost" size="sm" asChild className="-ml-2">
            <Link href="/practice-hub">
              <ArrowLeft className="mr-2 h-4 w-4" />
              Back to History
            </Link>
          </Button>
          <div className="flex gap-2">
             <div className="text-xs font-medium px-3 py-1 rounded-full bg-primary/10 text-primary uppercase tracking-wider font-bold">
               Session #{session.id}
             </div>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div className="md:col-span-2 space-y-8">
            <Card className="border-none shadow-lg overflow-hidden">
              <CardHeader className="border-b bg-muted/30 pb-4">
                <div className="flex items-center gap-2 text-primary mb-1">
                  <FileText className="h-4 w-4" />
                  <span className="text-xs font-bold uppercase tracking-widest">Original Passage</span>
                </div>
                <CardTitle className="text-2xl">{session.reading_category.name}</CardTitle>
                <div className="flex flex-wrap gap-4 mt-2 text-xs font-bold text-muted-foreground uppercase tracking-wider">
                  <div className="flex items-center gap-1">
                    <Globe className="h-3.5 w-3.5" />
                    {session.language}
                  </div>
                  <div className="flex items-center gap-1">
                    <Calendar className="h-3.5 w-3.5" />
                    {new Date(session.created_at).toLocaleDateString()}
                  </div>
                </div>
              </CardHeader>
              <CardContent className="pt-8">
                <div className="p-8 rounded-2xl bg-muted/20 border border-primary/5 text-xl leading-relaxed italic text-foreground/80 shadow-inner relative">
                  <div className="absolute top-4 left-4 text-4xl text-primary/10 font-serif">"</div>
                  {session.generated_text}
                  {getAiAudioUrl() && (
                    <div className="mt-8 pt-8 border-t border-primary/10">
                      <div className="flex items-center gap-2 mb-3">
                        <Sparkles className="h-4 w-4 text-primary" />
                        <span className="text-xs font-black uppercase tracking-widest text-primary">Native Speaker Comparison</span>
                      </div>
                      <div className="bg-background rounded-xl p-3 border shadow-sm flex items-center gap-4">
                        <div className="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                          <Play className="h-4 w-4 text-primary fill-current" />
                        </div>
                        <audio src={getAiAudioUrl()!} controls className="w-full h-8" />
                      </div>
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>

            <Card className="border-none shadow-lg overflow-hidden">
              <CardHeader className="border-b bg-muted/30 pb-4">
                 <div className="flex items-center gap-2 text-primary mb-1">
                  <Mic className="h-4 w-4" />
                  <span className="text-xs font-bold uppercase tracking-widest">Your Performance</span>
                </div>
                <CardTitle className="text-xl">Voice Playback & AI Analysis</CardTitle>
              </CardHeader>
              <CardContent className="pt-8 space-y-10">
                {session.recordings.map((recording) => (
                  <div key={recording.id} className="space-y-8">
                    <div className="flex items-center gap-4 bg-primary/5 p-6 rounded-2xl border border-primary/10 shadow-sm">
                      <div className="h-12 w-12 rounded-full bg-primary flex items-center justify-center shadow-lg">
                        <Play className="h-5 w-5 text-primary-foreground fill-current ml-1" />
                      </div>
                      <audio src={getAudioUrl(recording)} controls className="w-full h-10" />
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-10">
                      <div className="space-y-4">
                        <div className="flex items-center gap-2 text-primary font-black uppercase tracking-widest text-xs">
                          <CheckCircle className="h-4 w-4" />
                          <span>Coaching Feedback</span>
                        </div>
                        <div className="text-base leading-relaxed text-muted-foreground bg-muted/30 p-6 rounded-2xl border">
                          {session.ai_feedback}
                        </div>
                      </div>
                      <div className="space-y-6">
                        <div className="flex items-center gap-2 text-primary font-black uppercase tracking-widest text-xs">
                          <BarChart3 className="h-4 w-4" />
                          <span>Detailed Scoring</span>
                        </div>
                        <div className="space-y-5 bg-card p-6 rounded-2xl border shadow-sm">
                          {[
                            { label: 'Pronunciation', score: session.pronunciation_score, color: 'bg-emerald-500' },
                            { label: 'Intonation', score: session.intonation_score, color: 'bg-primary' },
                            { label: 'Grammar', score: session.grammar_score, color: 'bg-amber-500' }
                          ].map((stat) => (
                            <div key={stat.label} className="space-y-2">
                              <div className="flex justify-between items-end">
                                <span className="text-xs font-bold uppercase text-muted-foreground">{stat.label}</span>
                                <span className="text-lg font-black tabular-nums">{stat.score}%</span>
                              </div>
                              <div className="h-2 w-full bg-muted rounded-full overflow-hidden shadow-inner">
                                <div 
                                  className={cn("h-full transition-all duration-1000 ease-out", stat.color)}
                                  style={{ width: `${stat.score}%` }}
                                />
                              </div>
                            </div>
                          ))}
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </CardContent>
            </Card>
          </div>

          <div className="space-y-8">
            <Card className="bg-primary text-primary-foreground border-none shadow-xl relative overflow-hidden group">
              <div className="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-1000" />
              <CardHeader>
                <CardTitle className="text-sm font-black uppercase tracking-widest opacity-80">Fluency Score</CardTitle>
                <CardDescription className="text-primary-foreground/60 font-medium">Session Average</CardDescription>
              </CardHeader>
              <CardContent className="text-center pb-10">
                <div className="text-7xl font-black mb-2 drop-shadow-sm tabular-nums">
                  {Math.round((session.pronunciation_score + session.intonation_score + session.grammar_score) / 3)}%
                </div>
                <div className="inline-block px-4 py-1.5 rounded-full bg-white/20 text-xs font-black uppercase tracking-widest backdrop-blur-md">
                  Professional
                </div>
              </CardContent>
            </Card>

            <Card className="bg-muted/30 border-none shadow-inner">
              <CardHeader>
                <CardTitle className="text-xs font-black uppercase flex items-center gap-2 tracking-[0.2em] text-muted-foreground">
                  <Info className="h-4 w-4 text-primary" />
                  Growth Plan
                </CardTitle>
              </CardHeader>
              <CardContent className="text-sm space-y-5 text-muted-foreground leading-relaxed">
                <div className="flex gap-3">
                  <div className="h-5 w-5 rounded-full bg-primary/10 text-primary flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">1</div>
                  <p>
                    Your <strong>{
                      [
                        { l: 'pronunciation', s: session.pronunciation_score },
                        { l: 'intonation', s: session.intonation_score },
                        { l: 'grammar', s: session.grammar_score }
                      ].sort((a,b) => a.s - b.s)[0].l
                    }</strong> needs the most attention. Re-listen to the AI sample.
                  </p>
                </div>
                <div className="flex gap-3">
                  <div className="h-5 w-5 rounded-full bg-primary/10 text-primary flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">2</div>
                  <p>Practice the difficult phrases identified by the AI at a slower pace before trying full speed.</p>
                </div>
                <div className="flex gap-3">
                  <div className="h-5 w-5 rounded-full bg-primary/10 text-primary flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">3</div>
                  <p>Record this passage again tomorrow to measure your retention and progress.</p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </main>
  );
}

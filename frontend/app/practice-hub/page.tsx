"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { Card, CardHeader, CardTitle, CardContent, CardDescription, CardFooter } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { 
  ChevronRight, 
  Mic2, 
  Trophy, 
  History, 
  Clock, 
  Languages, 
  Search,
  ArrowRight,
  Filter,
  LayoutGrid,
  List
} from "lucide-react";
import { cn } from "@/lib/utils";

interface ReadingSession {
  id: number;
  language: string;
  pronunciation_score: number;
  intonation_score: number;
  grammar_score: number;
  created_at: string;
  reading_category: {
    name: string;
  };
}

export default function PracticeHubPage() {
  const [sessions, setSessions] = useState<ReadingSession[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [viewMode, setViewMode] = useState<"grid" | "list">("grid");
  const [searchQuery, setSearchQuery] = useState("");

  useEffect(() => {
    const fetchSessions = async () => {
      try {
        const response = await fetch("http://127.0.0.1:8000/api/reading-sessions");
        const data = await response.json();
        setSessions(data.data || []);
      } catch (error) {
        console.error("Failed to fetch sessions:", error);
      } finally {
        setIsLoading(false);
      }
    };
    fetchSessions();
  }, []);

  const getAverageScore = (session: ReadingSession) => {
    return Math.round((session.pronunciation_score + session.intonation_score + session.grammar_score) / 3);
  };

  const getScoreColor = (score: number) => {
    if (score >= 90) return "text-emerald-500 bg-emerald-500/10 border-emerald-500/20";
    if (score >= 70) return "text-amber-500 bg-amber-500/10 border-amber-500/20";
    return "text-rose-500 bg-rose-500/10 border-rose-500/20";
  };

  const filteredSessions = sessions.filter(s => 
    s.reading_category.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    s.language.toLowerCase().includes(searchQuery.toLowerCase())
  );

  if (isLoading) {
    return (
      <main className="h-[calc(100vh-64px)] flex flex-col items-center justify-center space-y-4">
        <div className="h-12 w-12 rounded-full border-4 border-primary border-t-transparent animate-spin" />
        <p className="text-muted-foreground font-medium animate-pulse text-lg uppercase tracking-widest">Gathering your progress...</p>
      </main>
    );
  }

  return (
    <main className="h-[calc(100vh-64px)] flex flex-col overflow-hidden bg-background">
      {/* Scrollable Container for the whole content but with fixed outer bounds */}
      <div className="flex-1 overflow-y-auto custom-scrollbar px-4 lg:px-8 py-8 space-y-10">
        
        {/* Header & New Session Button */}
        <div className="max-w-7xl mx-auto w-full flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div className="space-y-1">
            <div className="flex items-center gap-2 text-primary">
              <History className="h-4 w-4" />
              <span className="text-[10px] font-black uppercase tracking-[0.3em]">History</span>
            </div>
            <h1 className="text-3xl font-black tracking-tight">Practice Hub</h1>
          </div>
          <Button asChild size="lg" className="rounded-2xl shadow-xl shadow-primary/20 hover:scale-[1.02] transition-all">
            <Link href="/">
              <Mic2 className="mr-2 h-5 w-5" />
              Start New Lesson
            </Link>
          </Button>
        </div>

        {/* Dynamic Stats Row - Ultra Compact */}
        <div className="max-w-7xl mx-auto w-full">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            {[
              { label: "Lessons", value: sessions.length, icon: History, color: "text-blue-500", bg: "bg-blue-500/5" },
              { label: "Hours", value: "2.4h", icon: Clock, color: "text-purple-500", bg: "bg-purple-500/5" },
              { label: "Avg. Score", value: sessions.length ? `${Math.round(sessions.reduce((acc, s) => acc + getAverageScore(s), 0) / sessions.length)}%` : "0%", icon: Trophy, color: "text-amber-500", bg: "bg-amber-500/5" },
              { label: "Languages", value: new Set(sessions.map(s => s.language)).size, icon: Languages, color: "text-emerald-500", bg: "bg-emerald-500/5" }
            ].map((stat) => (
              <div key={stat.label} className={cn("p-4 rounded-3xl border border-border/50 flex flex-col gap-1 transition-all hover:bg-card", stat.bg)}>
                <div className="flex items-center justify-between">
                  <stat.icon className={cn("h-4 w-4", stat.color)} />
                  <span className="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">{stat.label}</span>
                </div>
                <p className="text-xl font-black">{stat.value}</p>
              </div>
            ))}
          </div>
        </div>

        {/* Toolbar: Search, Filters, View Toggle */}
        <div className="max-w-7xl mx-auto w-full sticky top-0 z-10 py-2 bg-background/80 backdrop-blur-md">
          <div className="flex flex-col sm:flex-row items-center justify-between gap-4 p-2 rounded-3xl border bg-card/50 shadow-sm">
            <div className="relative w-full sm:w-96">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <input 
                type="text" 
                placeholder="Find a past session..." 
                className="w-full pl-11 pr-4 py-2.5 bg-transparent border-none text-sm focus:ring-0 outline-none placeholder:text-muted-foreground/60 font-medium"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
            
            <div className="flex items-center gap-2 self-end sm:self-auto pr-2">
              <div className="flex bg-muted p-1 rounded-xl">
                <Button 
                  variant={viewMode === "grid" ? "secondary" : "ghost"} 
                  size="icon" 
                  className="h-8 w-8 rounded-lg"
                  onClick={() => setViewMode("grid")}
                >
                  <LayoutGrid className="h-4 w-4" />
                </Button>
                <Button 
                  variant={viewMode === "list" ? "secondary" : "ghost"} 
                  size="icon" 
                  className="h-8 w-8 rounded-lg"
                  onClick={() => setViewMode("list")}
                >
                  <List className="h-4 w-4" />
                </Button>
              </div>
              <div className="h-6 w-px bg-border mx-1" />
              <Button variant="outline" size="sm" className="rounded-xl h-10 gap-2 font-bold border-muted-foreground/20">
                <Filter className="h-3.5 w-3.5" />
                Filter
              </Button>
            </div>
          </div>
        </div>

        {/* Sessions Area */}
        <div className="max-w-7xl mx-auto w-full pb-20">
          {filteredSessions.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-20 text-center space-y-6">
              <div className="h-24 w-24 rounded-full bg-muted flex items-center justify-center border-4 border-dashed border-muted-foreground/20">
                <History className="h-10 w-10 text-muted-foreground opacity-20" />
              </div>
              <div className="space-y-2">
                <h3 className="text-xl font-bold">No sessions found</h3>
                <p className="text-muted-foreground text-sm max-w-xs mx-auto">
                  {searchQuery ? "Try refining your search terms." : "You haven't completed any lessons yet. Let's change that!"}
                </p>
              </div>
              {!searchQuery && (
                <Button asChild className="rounded-full px-8 shadow-lg">
                  <Link href="/">Start Now</Link>
                </Button>
              )}
            </div>
          ) : viewMode === "grid" ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {filteredSessions.map((session) => {
                const avgScore = getAverageScore(session);
                return (
                  <Card key={session.id} className="group overflow-hidden border-none shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 rounded-[2rem] bg-card/50 backdrop-blur-sm ring-1 ring-border/50">
                    <div className={cn("h-1.5 w-full", 
                      avgScore >= 90 ? "bg-emerald-500" : avgScore >= 70 ? "bg-amber-500" : "bg-rose-500"
                    )} />
                    <CardHeader className="pb-4">
                      <div className="flex justify-between items-start">
                        <div className="space-y-1">
                          <div className="flex items-center gap-2">
                            <span className="px-2 py-0.5 rounded-full bg-primary/10 text-[9px] font-black text-primary uppercase tracking-tighter">
                              {session.language}
                            </span>
                            <span className="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                              {session.reading_category.name}
                            </span>
                          </div>
                          <CardTitle className="text-xl font-black pt-1">
                            {new Date(session.created_at).toLocaleDateString(undefined, { 
                              month: 'short', 
                              day: 'numeric'
                            })}
                          </CardTitle>
                        </div>
                        <div className={cn("h-12 w-12 rounded-2xl flex flex-col items-center justify-center border tabular-nums shadow-sm", getScoreColor(avgScore))}>
                          <span className="text-lg font-black leading-none">{avgScore}</span>
                          <span className="text-[8px] font-bold uppercase opacity-70">%</span>
                        </div>
                      </div>
                    </CardHeader>
                    <CardContent className="pb-6">
                      <div className="flex gap-4 items-center overflow-x-auto no-scrollbar py-1">
                        {[
                          { l: "Voice", s: session.pronunciation_score },
                          { l: "Flow", s: session.intonation_score },
                          { l: "Gram", s: session.grammar_score }
                        ].map(stat => (
                          <div key={stat.l} className="flex flex-col items-center gap-1 min-w-[60px]">
                            <div className="w-full h-1 bg-muted rounded-full overflow-hidden">
                              <div className="h-full bg-primary/40" style={{ width: `${stat.s}%` }} />
                            </div>
                            <span className="text-[9px] font-bold text-muted-foreground uppercase tracking-wider">{stat.l}</span>
                          </div>
                        ))}
                      </div>
                    </CardContent>
                    <CardFooter className="p-0 border-t border-border/20">
                      <Button variant="ghost" className="w-full h-14 rounded-none bg-muted/10 group-hover:bg-primary group-hover:text-primary-foreground transition-all gap-2 font-bold" asChild>
                        <Link href={`/practice-hub/${session.id}`}>
                          View Details
                          <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                        </Link>
                      </Button>
                    </CardFooter>
                  </Card>
                );
              })}
            </div>
          ) : (
            <div className="space-y-3">
              {filteredSessions.map((session) => {
                const avgScore = getAverageScore(session);
                return (
                  <Link key={session.id} href={`/practice-hub/${session.id}`} className="block group">
                    <div className="flex items-center justify-between p-4 rounded-2xl border bg-card/50 hover:bg-card hover:shadow-md transition-all">
                      <div className="flex items-center gap-4">
                        <div className={cn("h-12 w-12 rounded-xl flex items-center justify-center font-black", getScoreColor(avgScore))}>
                          {avgScore}%
                        </div>
                        <div className="space-y-0.5">
                          <p className="font-bold">{session.reading_category.name}</p>
                          <div className="flex items-center gap-2 text-xs text-muted-foreground">
                            <span className="font-bold text-primary uppercase">{session.language}</span>
                            <span>•</span>
                            <span>{new Date(session.created_at).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                          </div>
                        </div>
                      </div>
                      <ChevronRight className="h-5 w-5 text-muted-foreground group-hover:text-primary transition-colors group-hover:translate-x-1" />
                    </div>
                  </Link>
                );
              })}
            </div>
          )}
        </div>
      </div>
    </main>
  );
}

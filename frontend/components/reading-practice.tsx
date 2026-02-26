"use client";

import { useState, useRef, useEffect } from "react";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { 
  Mic, 
  Mic2,
  Square, 
  Sparkles, 
  RotateCcw, 
  CheckCircle2, 
  Play, 
  Pause,
  AlertCircle,
  BarChart,
  BookOpen,
  Globe2,
  BrainCircuit,
  Save,
  Loader2,
  Settings
} from "lucide-react";
import { cn } from "@/lib/utils";
import { useToast } from "@/components/ui/use-toast";

interface ReadingCategory {
  id: number;
  name: string;
}

export function ReadingPractice() {
  const { toast } = useToast();
  const [topic, setTopic] = useState("");
  const [level, setLevel] = useState("beginner");
  const [language, setLanguage] = useState("en-US");
  const [selectedVoice, setSelectedVoice] = useState("alloy");
  const [readingCategoryId, setReadingCategoryId] = useState<number | null>(null);
  const [categories, setCategories] = useState<ReadingCategory[]>([]);
  const [generatedText, setGeneratedText] = useState("");
  const [generatedAudioUrl, setGeneratedAudioUrl] = useState<string | null>(null);
  const [feedback, setFeedback] = useState<any>(null);
  const [isRecording, setIsRecording] = useState(false);
  const [audioBlob, setAudioBlob] = useState<Blob | null>(null);
  const [recordingTime, setRecordingTime] = useState(0);
  const mediaRecorder = useRef<MediaRecorder | null>(null);
  const timerRef = useRef<any>(null);

  const [isGenerating, setIsGenerating] = useState(false);
  const [isSynthesizing, setIsSynthesizing] = useState(false);
  const [isAnalyzing, setIsAnalyzing] = useState(false);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    const fetchCategories = async () => {
      try {
        const response = await fetch("http://127.0.0.1:8000/api/reading-practice/categories");
        const data = await response.json();
        setCategories(data);
        if (data.length > 0) {
          setReadingCategoryId(data[0].id);
        }
      } catch (error) {
        console.error("Failed to fetch categories:", error);
      }
    };
    fetchCategories();
  }, []);

  useEffect(() => {
    if (isRecording) {
      timerRef.current = setInterval(() => {
        setRecordingTime((prev) => prev + 1);
      }, 1000);
    } else {
      clearInterval(timerRef.current);
      setRecordingTime(0);
    }
    return () => clearInterval(timerRef.current);
  }, [isRecording]);

  const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, "0")}`;
  };

  const generateText = async () => {
    setIsGenerating(true);
    setGeneratedText("");
    setGeneratedAudioUrl(null);
    setFeedback(null);
    setAudioBlob(null);
    try {
      const response = await fetch("http://127.0.0.1:8000/api/reading-practice/generate-text", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify({ topic, level, language, voice: selectedVoice }),
      });
      const data = await response.json();
      setGeneratedText(data.text);
      if (data.audio_url) {
        setGeneratedAudioUrl(`http://127.0.0.1:8000${data.audio_url}`);
      }
    } catch (error) {
      console.error("Failed to generate text:", error);
    } finally {
      setIsGenerating(false);
    }
  };

  const startRecording = async () => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      mediaRecorder.current = new MediaRecorder(stream);
      mediaRecorder.current.start();

      const audioChunks: Blob[] = [];
      mediaRecorder.current.addEventListener("dataavailable", (event) => {
        audioChunks.push(event.data);
      });

      mediaRecorder.current.addEventListener("stop", () => {
        const audioBlob = new Blob(audioChunks, { type: "audio/mp3" });
        setAudioBlob(audioBlob);
      });

      setIsRecording(true);
    } catch (error) {
      console.error("Failed to start recording:", error);
    }
  };

  const stopRecording = () => {
    if (mediaRecorder.current) {
      mediaRecorder.current.stop();
      setIsRecording(false);
    }
  };

  const analyzeRecording = async () => {
    if (!audioBlob) return;

    setIsAnalyzing(true);
    const formData = new FormData();
    formData.append("audio", audioBlob, "recording.mp3");
    formData.append("text", generatedText);
    formData.append("language", language);

    try {
      const response = await fetch("http://127.0.0.1:8000/api/reading-practice/analyze-recording", {
        method: "POST",
        headers: {
          "Accept": "application/json",
        },
        body: formData,
      });
      const data = await response.json();
      setFeedback(data);
    } catch (error) {
      console.error("Failed to analyze recording:", error);
    } finally {
      setIsAnalyzing(false);
    }
  };

  const saveSession = async () => {
    if (!audioBlob || !feedback || !readingCategoryId) return;

    setIsSaving(true);
    const formData = new FormData();
    formData.append("audio", audioBlob, "recording.mp3");
    formData.append("text", generatedText);
    formData.append("feedback", JSON.stringify(feedback));
    formData.append("user_id", "1");
    formData.append("reading_category_id", readingCategoryId.toString());
    formData.append("language", language);
    if (generatedAudioUrl) {
      formData.append("ai_audio_url", generatedAudioUrl.replace("http://127.0.0.1:8000", ""));
    }

    try {
      const response = await fetch("http://127.0.0.1:8000/api/reading-practice/save", {
        method: "POST",
        headers: {
          "Accept": "application/json",
        },
        body: formData,
      });

      if (response.ok) {
        toast("Session saved successfully!", "success", "Great job!");
      } else {
        toast("Failed to save session.", "error", "System Error");
      }
    } catch (error) {
      console.error("Failed to save session:", error);
      toast("An unexpected error occurred while saving.", "error");
    } finally {
      setIsSaving(false);
    }
  };

  const getScoreColor = (score: number) => {
    if (score >= 90) return "text-emerald-500";
    if (score >= 70) return "text-amber-500";
    return "text-rose-500";
  };

  return (
    <div className="py-8 px-4 max-w-4xl mx-auto space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-700">
      {/* Intro Header */}
      <div className="text-center space-y-3">
        <h1 className="text-4xl font-extrabold tracking-tight sm:text-5xl">
          Perfect Your <span className="text-primary italic">Fluency</span>
        </h1>
        <p className="text-muted-foreground text-lg max-w-2xl mx-auto">
          Choose a topic, generate a professional reading passage, and let our AI coach help you master your pronunciation.
        </p>
      </div>

      <div className="grid grid-cols-1 gap-8">
        {/* Setup Card */}
        <Card className="overflow-hidden border-none shadow-xl bg-card/50 backdrop-blur-sm">
          <div className="h-2 bg-primary" />
          <CardHeader>
            <div className="flex items-center gap-2 mb-1">
              <Settings className="h-4 w-4 text-primary" />
              <span className="text-xs font-bold uppercase tracking-widest text-primary">Configuration</span>
            </div>
            <CardTitle className="text-2xl">Create Your Lesson</CardTitle>
            <CardDescription>Tailor the reading material to your specific interests and level.</CardDescription>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="space-y-2">
              <Label htmlFor="topic" className="text-sm font-semibold flex items-center gap-2">
                <Sparkles className="h-3 w-3 text-primary" />
                What would you like to read about?
              </Label>
              <Textarea
                id="topic"
                placeholder="e.g. A space explorer discovering a new planet, or a review of a classic Italian movie..."
                className="min-h-[100px] resize-none focus-visible:ring-primary/50 transition-all text-base p-4"
                value={topic}
                onChange={(e) => setTopic(e.target.value)}
                disabled={isGenerating || isAnalyzing || isSaving}
              />
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div className="space-y-2">
                <Label htmlFor="level" className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Difficulty</Label>
                <Select value={level} onValueChange={setLevel} disabled={isGenerating || isAnalyzing || isSaving}>
                  <SelectTrigger id="level" className="h-11 bg-background/50 border-muted-foreground/20">
                    <SelectValue placeholder="Level" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="beginner">Beginner</SelectItem>
                    <SelectItem value="intermediate">Intermediate</SelectItem>
                    <SelectItem value="advanced">Advanced</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              
              <div className="space-y-2">
                <Label htmlFor="language" className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Language</Label>
                <Select value={language} onValueChange={setLanguage} disabled={isGenerating || isAnalyzing || isSaving}>
                  <SelectTrigger id="language" className="h-11 bg-background/50 border-muted-foreground/20">
                    <div className="flex items-center gap-2">
                      <Globe2 className="h-3 w-3 text-primary" />
                      <SelectValue placeholder="Language" />
                    </div>
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="en-US">English (US)</SelectItem>
                    <SelectItem value="en-GB">English (UK)</SelectItem>
                    <SelectItem value="es-ES">Spanish</SelectItem>
                    <SelectItem value="it-IT">Italian</SelectItem>
                    <SelectItem value="fr-FR">French</SelectItem>
                    <SelectItem value="de-DE">German</SelectItem>
                    <SelectItem value="nl-NL">Dutch</SelectItem>
                    <SelectItem value="ru-RU">Russian</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2">
                <Label htmlFor="voice" className="text-xs font-bold uppercase tracking-wider text-muted-foreground">AI Voice</Label>
                <Select value={selectedVoice} onValueChange={setSelectedVoice} disabled={isGenerating || isAnalyzing || isSaving}>
                  <SelectTrigger id="voice" className="h-11 bg-background/50 border-muted-foreground/20">
                    <div className="flex items-center gap-2">
                      <Mic2 className="h-3 w-3 text-primary" />
                      <SelectValue placeholder="Voice" />
                    </div>
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="alloy">Alloy (Neutral)</SelectItem>
                    <SelectItem value="echo">Echo (Confident)</SelectItem>
                    <SelectItem value="fable">Fable (British)</SelectItem>
                    <SelectItem value="onyx">Onyx (Deep)</SelectItem>
                    <SelectItem value="nova">Nova (Bright)</SelectItem>
                    <SelectItem value="shimmer">Shimmer (Soft)</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2">
                <Label htmlFor="category" className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Context</Label>
                <Select
                  value={readingCategoryId?.toString()}
                  onValueChange={(value) => setReadingCategoryId(Number(value))}
                  disabled={isGenerating || isAnalyzing || isSaving}
                >
                  <SelectTrigger id="category" className="h-11 bg-background/50 border-muted-foreground/20">
                    <div className="flex items-center gap-2">
                      <BookOpen className="h-3 w-3 text-primary" />
                      <SelectValue placeholder="Category" />
                    </div>
                  </SelectTrigger>
                  <SelectContent>
                    {categories.map((category) => (
                      <SelectItem key={category.id} value={category.id.toString()}>
                        {category.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardContent>
          <CardFooter className="bg-muted/30 pt-6">
            <Button 
              onClick={generateText} 
              className="w-full h-12 text-base font-bold shadow-lg transition-all hover:scale-[1.01] active:scale-[0.99]" 
              disabled={isGenerating || isAnalyzing || isSaving || !topic}
            >
              {isGenerating ? (
                <>
                  <Loader2 className="mr-2 h-5 w-5 animate-spin" />
                  Crafting your story...
                </>
              ) : (
                <>
                  <Sparkles className="mr-2 h-5 w-5" />
                  Generate Practice Text
                </>
              )}
            </Button>
          </CardFooter>
        </Card>

        {/* Generated Text Section */}
        {generatedText && (
          <div className="space-y-8 animate-in fade-in slide-in-from-bottom-8 duration-1000">
            <Card className="border-primary/20 bg-primary/5 shadow-inner overflow-hidden">
              <CardHeader className="flex flex-row items-center justify-between space-y-0">
                <CardTitle className="flex items-center gap-2">
                  <div className="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                    <BookOpen className="h-4 w-4 text-primary" />
                  </div>
                  Practice Material
                </CardTitle>
                <Button variant="ghost" size="sm" onClick={() => setGeneratedText("")} className="text-muted-foreground">
                  <RotateCcw className="h-4 w-4 mr-2" />
                  Start Over
                </Button>
              </CardHeader>
              <CardContent className="space-y-8 pb-10">
                <div className="relative group">
                  <div className="absolute -inset-1 bg-gradient-to-r from-primary/20 to-primary/5 rounded-2xl blur-lg opacity-25 group-hover:opacity-50 transition duration-1000" />
                  <div className="relative p-8 rounded-xl bg-white/80 dark:bg-black/20 border shadow-sm backdrop-blur-sm">
                    <p className="text-xl md:text-2xl font-medium leading-relaxed tracking-tight text-foreground/90 first-letter:text-4xl first-letter:font-bold first-letter:text-primary">
                      {generatedText}
                    </p>
                    {generatedAudioUrl && (
                      <div className="mt-6 pt-6 border-t border-primary/10 flex flex-col items-start gap-3">
                        <Label className="text-xs font-bold uppercase tracking-widest text-primary flex items-center gap-2">
                          <Play className="h-3 w-3 fill-current" />
                          Listen to AI Native Speaker
                        </Label>
                        <audio src={generatedAudioUrl} controls className="w-full h-8" />
                      </div>
                    )}
                  </div>
                </div>

                <div className="flex flex-col items-center justify-center gap-6">
                  {isRecording ? (
                    <div className="flex flex-col items-center gap-4 animate-pulse">
                      <div className="relative">
                        <div className="absolute inset-0 rounded-full bg-destructive animate-ping opacity-25" />
                        <Button 
                          onClick={stopRecording} 
                          variant="destructive" 
                          size="lg" 
                          className="h-20 w-20 rounded-full shadow-2xl relative z-10 p-0"
                        >
                          <Square className="h-8 w-8 fill-current" />
                        </Button>
                      </div>
                      <div className="flex items-center gap-2 bg-destructive/10 px-4 py-1.5 rounded-full text-destructive font-mono text-xl font-bold border border-destructive/20">
                        <div className="h-2 w-2 rounded-full bg-destructive animate-pulse" />
                        {formatTime(recordingTime)}
                      </div>
                      <p className="text-sm font-medium text-destructive animate-pulse uppercase tracking-widest">Recording in progress...</p>
                    </div>
                  ) : (
                    <div className="flex flex-col items-center gap-4">
                      <Button 
                        onClick={startRecording} 
                        size="lg" 
                        className="h-24 w-24 rounded-full shadow-2xl transition-all hover:scale-110 active:scale-95 group p-0"
                        disabled={isAnalyzing || isSaving}
                      >
                        <Mic className="h-10 w-10 transition-transform group-hover:scale-110" />
                      </Button>
                      <p className="text-sm font-bold text-muted-foreground uppercase tracking-widest">Click to start recording</p>
                    </div>
                  )}
                </div>

                {audioBlob && !isRecording && (
                  <div className="flex flex-col items-center gap-6 pt-10 border-t border-primary/10">
                    <div className="w-full max-w-md bg-background rounded-2xl p-4 shadow-sm border flex items-center gap-4">
                       <div className="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                          <Play className="h-5 w-5 text-primary fill-current" />
                       </div>
                       <audio src={URL.createObjectURL(audioBlob)} controls className="flex-1 h-10" />
                    </div>
                    <Button 
                      onClick={analyzeRecording} 
                      variant="outline" 
                      className="w-full max-w-sm h-12 text-base font-bold border-primary text-primary hover:bg-primary hover:text-primary-foreground shadow-sm transition-all" 
                      disabled={isAnalyzing || isSaving}
                    >
                      {isAnalyzing ? (
                        <>
                          <Loader2 className="mr-2 h-5 w-5 animate-spin" />
                          AI is analyzing your voice...
                        </>
                      ) : (
                        <>
                          <BrainCircuit className="mr-2 h-5 w-5" />
                          Get Expert Feedback
                        </>
                      )}
                    </Button>
                  </div>
                )}
              </CardContent>
            </Card>

            {/* AI Feedback Section */}
            {feedback && (
              <Card className="border-none shadow-2xl bg-gradient-to-br from-emerald-500/5 via-primary/5 to-primary/10 overflow-hidden animate-in zoom-in-95 fade-in duration-1000">
                <div className="h-2 bg-gradient-to-r from-emerald-500 to-primary" />
                <CardHeader>
                  <div className="flex items-center gap-2 mb-1">
                    <BrainCircuit className="h-4 w-4 text-emerald-500" />
                    <span className="text-xs font-bold uppercase tracking-widest text-emerald-600">AI Coach Evaluation</span>
                  </div>
                  <CardTitle className="text-2xl flex items-center justify-between">
                    Performance Summary
                    <div className="flex items-center gap-1 text-sm font-bold text-emerald-600 bg-emerald-100 px-3 py-1 rounded-full">
                      <CheckCircle2 className="h-4 w-4" />
                      Completed
                    </div>
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-8">
                  {/* Score Circles/Cards */}
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {[
                      { label: "Pronunciation", score: feedback.pronunciation, icon: Mic, desc: "Accuracy of sounds" },
                      { label: "Intonation", score: feedback.intonation, icon: BarChart, desc: "Natural rhythm" },
                      { label: "Grammar", score: feedback.grammar, icon: CheckCircle2, desc: "Structural correctness" }
                    ].map((item) => (
                      <div key={item.label} className="relative group bg-card p-6 rounded-2xl border shadow-sm transition-all hover:shadow-md">
                        <div className="flex justify-between items-start mb-4">
                           <div className="p-2 rounded-lg bg-muted text-muted-foreground group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                              <item.icon className="h-5 w-5" />
                           </div>
                           <div className={cn("text-3xl font-black tabular-nums", getScoreColor(item.score))}>
                             {item.score}%
                           </div>
                        </div>
                        <h4 className="font-bold text-sm uppercase tracking-wide mb-1">{item.label}</h4>
                        <p className="text-xs text-muted-foreground">{item.desc}</p>
                        <div className="mt-4 h-1.5 w-full bg-muted rounded-full overflow-hidden">
                           <div 
                             className={cn("h-full transition-all duration-1000 ease-out", 
                               item.score >= 90 ? "bg-emerald-500" : item.score >= 70 ? "bg-amber-500" : "bg-rose-500"
                             )} 
                             style={{ width: `${item.score}%` }}
                           />
                        </div>
                      </div>
                    ))}
                  </div>

                  <div className="space-y-4">
                    <div className="flex items-center gap-2 text-primary font-bold">
                      <AlertCircle className="h-5 w-5" />
                      <span>Coaching Insights</span>
                    </div>
                    <div className="rounded-2xl bg-white/50 dark:bg-black/20 border border-primary/10 p-6 shadow-sm leading-relaxed text-lg">
                      {feedback.feedback}
                    </div>
                  </div>
                </CardContent>
                <CardFooter className="bg-primary/5 pt-6 pb-6 border-t border-primary/10">
                  <Button 
                    onClick={saveSession} 
                    className="w-full h-14 text-lg font-bold bg-primary hover:bg-primary/90 shadow-xl transition-all hover:scale-[1.01]" 
                    disabled={isSaving}
                  >
                    {isSaving ? (
                      <>
                        <Loader2 className="mr-2 h-6 w-6 animate-spin" />
                        Saving to your journey...
                      </>
                    ) : (
                      <>
                        <Save className="mr-2 h-6 w-6" />
                        Save Session to Practice Hub
                      </>
                    )}
                  </Button>
                </CardFooter>
              </Card>
            )}
          </div>
        )}
      </div>
    </div>
  );
}

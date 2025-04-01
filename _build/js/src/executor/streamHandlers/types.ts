import type { TextData } from '../services';
import type { ChunkStream } from '../types';

export type StreamHandler = (
  chunk: string,
  buffer: string,
  currentData: TextData,
  onChunkStream?: ChunkStream<TextData>,
) => { buffer: string; currentData: TextData };

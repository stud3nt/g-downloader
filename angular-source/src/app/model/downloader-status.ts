import { DownloaderStatus as DownloaderStatusEnum } from "../enum/downloader-status";
import { ParsedFile } from "./parsed-file";

export class DownloaderStatus {

	queuedFiles: number = 0;

	downloadedFiles: number = 0;

	status: string = DownloaderStatusEnum.Idle;

	waitingFiles: ParsedFile[] = [];

}
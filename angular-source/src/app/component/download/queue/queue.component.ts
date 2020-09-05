import { Component, OnInit } from '@angular/core';
import { ContentHeaderDataService } from "../../../service/data/content-header-data.service";
import { DownloaderService } from "../../../service/downloader.service";
import { ParsedFile } from "../../../model/parsed-file";
import { CacheService } from "../../../service/cache.service";
import { JsonResponse } from "../../../model/json-response";
import {QueueSettings} from "../../../model/queue-settings";

@Component({
  selector: 'app-queue',
  templateUrl: './queue.component.html',
  styleUrls: ['./queue.component.scss']
})
export class QueueComponent implements OnInit {

    public queueFiles: ParsedFile[] = [];

    public queueSettings: QueueSettings = new QueueSettings();

    static cacheStatusKey: string = 'queue_status_data';

    constructor(
        private headerData: ContentHeaderDataService,
        private downloaderService: DownloaderService,
        private cacheService: CacheService
    ) {}

    ngOnInit() {
        this.headerData.setElement('title1', 'Download queue');
        this.headerData.setElement('title2', 'Detailed list of queued and operated files');
    }

    public startDownload(): void {
        this.downloaderService.startDownloadProcess();
    }

    public stopDownload(): void {
        this.downloaderService.stopDownloadProcess();
    }

    public checkFilesStatus(): void {
        let status = this.cacheService.get(QueueComponent.cacheStatusKey);
    }

    /**
     * Load files list
     */
    private getQueueFiles(): void {
        this.downloaderService.getQueuedFilesList(this.queueSettings).subscribe((response: JsonResponse) => {
            if (response.success()) {
                this.queueFiles = [];

                for (let fileData of response.data) {
                    this.queueFiles.push(new ParsedFile(fileData));
                }
            }
        }, (error) => {

        });
    }
}

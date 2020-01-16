<?php

namespace App\Manager\Object;

use App\Converter\EntityConverter;
use App\Entity\Parser\File;
use App\Enum\FileStatus;
use App\Manager\Base\EntityManager;
use App\Manager\SettingsManager;
use App\Model\ParsedFile;
use App\Model\ParserRequest;
use App\Repository\FileRepository;
use App\Utils\FilesHelper;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;

class FileManager extends EntityManager
{
    protected $entityName = 'Parser\File';

    /** @var FileRepository */
    protected $repository;

    /** @var SettingsManager */
    protected $settingsManager;

    /** @var EntityConverter */
    protected $entityConverter;

    /** @required */
    public function init(SettingsManager $settingsManager, EntityConverter $entityConverter)
    {
        $this->settingsManager = $settingsManager;
        $this->entityConverter = $entityConverter;
    }

    /**
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     */
    public function completeParsedStatuses(ParserRequest &$parserRequest) : ParserRequest
    {
        if ($parserRequest->files) {
            $filesIdentifiers = [];

            /* @var ParsedFile $file */
            foreach ($parserRequest->files as $file) {
                $filesIdentifiers[] = $file->getIdentifier();
            }

            $storedFilesArray = $this->repository->getQb()
                ->where('f.parser = :parserName')
                ->andWhere('f.identifier IN (:filesIdentifiers)')
                ->setParameter('parserName', $parserRequest->parser)
                ->setParameter('filesIdentifiers', $filesIdentifiers)
                ->getQuery()->getArrayResult();

            if ($storedFilesArray) {
                foreach ($parserRequest->files as $fileIndex => $file) {
                    $parserRequest->files[$fileIndex]->clearStatuses();

                    foreach ($storedFilesArray as $storedFile) {
                        if ($storedFile['identifier'] == $file->getIdentifier()) {
                            $parserRequest->files[$fileIndex]->addStatus(FileStatus::Queued);

                            if ($storedFile['downloadedAt']) {
                                $parserRequest->files[$fileIndex]->addStatus(FileStatus::Downloaded);
                            }
                        }
                    }
                }
            }
        }

        return $parserRequest;
    }

    /**
     * @param ParsedFile $parsedFile
     * @return bool
     * @throws \ReflectionException
     */
    public function toggleFileQueue(ParsedFile &$parsedFile): ParsedFile
    {
        $dbFile = $this->repository->findOneBy([
            'identifier' => $parsedFile->identifier,
            'parser' => $parsedFile->parser
        ]);

        if (!$dbFile) {
            $dbFile = new File();

            $this->entityConverter->setData($parsedFile, $dbFile);

            if ($this->save($dbFile)) {
                $parsedFile->statuses[] = FileStatus::Queued;
            }
        } else {
            $this->remove($dbFile);

            foreach ($parsedFile->statuses as $statusIndex => $status) {
                if ($status == FileStatus::Queued) {
                    unset($parsedFile->statuses[$statusIndex]);
                }
            }
        }

        return $parsedFile;
    }

    /**
     * Gets queued files (waiting for download);
     *
     * @param int $limit
     * @return array
     */
    public function getQueuedFiles(int $limit = 10, bool $asArray = false) : array
    {
        $queuedFiles = $this->repository->getFilesQb([
            'type' => 'queued',
            'limit' => $limit
        ])->getQuery()->getResult(
            ($asArray) ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT
        );

        if ($queuedFiles) {
            foreach ($queuedFiles as $key => $queuedFile) {
                if ($asArray)
                    $queuedFiles[$key]['textSize'] = FilesHelper::bytesToSize($queuedFile['size']);
                else
                    $queuedFiles[$key]->setTextSize(FilesHelper::bytesToSize($queuedFile->getSize()));
            }
        }

        return $queuedFiles;
    }

    /**
     * Return count of all queued files
     *
     * @return array
     * @throws NonUniqueResultException
     */
    public function getBasicFilesData() : array
    {
        $queuedCounts = $this->repository->getFilesQb([
            'select' => 'COUNT(f.id) as totalCount, SUM(f.size) as totalSize',
            'type' => 'queued'
        ])->setMaxResults(1)->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        $downloadedCounts = $this->repository->getFilesQb([
            'type' => 'downloaded',
            'select' => 'COUNT(f.id) as totalCount, SUM(f.size) as totalSize'
        ])->setMaxResults(1)->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        return [
            'queuedFilesCount' => $queuedCounts['totalCount'],
            'queuedFilesSize' => FilesHelper::bytesToSize($queuedCounts['totalSize']),
            'downloadedFilesCount' => $downloadedCounts['totalCount'],
            'downloadedFilesSize' => FilesHelper::bytesToSize($downloadedCounts['totalSize'])
        ];
    }

    /**
     * Updates downloaded file as saved;
     *
     * @param array $downloadedFiles
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateDownloadedFiles(array $downloadedFiles = []): void
    {
        if ($downloadedFiles) {
            /** @var File $downloadedFile */
            foreach ($downloadedFiles as $downloadedFile) {
                $this->em->persist(
                    $downloadedFile->setDownloadedAt(new \DateTime('now'))
                );
            }

            $this->em->flush();
        }
    }
}
